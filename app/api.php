<?php
/**
 * TalkMetrics REST API
 * 
 * Endpoints:
 *   GET  /api.php?action=health   - Health check
 *   GET  /api.php?action=stats    - Today's call statistics
 *   GET  /api.php?action=calls    - Recent calls (paginated)
 *   POST /api.php?action=webhook  - Receive call events from telephony provider
 */

require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['error' => 'Database unavailable', 'healthy' => false]);
    exit;
}

switch ($action) {
    case 'health':
        $health = ['healthy' => true, 'checks' => []];
        
        // MySQL check
        try {
            $pdo->query('SELECT 1');
            $health['checks']['mysql'] = 'ok';
        } catch (Exception $e) {
            $health['checks']['mysql'] = 'failed';
            $health['healthy'] = false;
        }
        
        // Redis check
        global $redis;
        if ($redis) {
            try {
                $redis->ping();
                $health['checks']['redis'] = 'ok';
            } catch (Exception $e) {
                $health['checks']['redis'] = 'failed';
                $health['healthy'] = false;
            }
        } else {
            $health['checks']['redis'] = 'unavailable';
        }
        
        $health['timestamp'] = date('c');
        http_response_code($health['healthy'] ? 200 : 503);
        echo json_encode($health);
        break;
        
    case 'stats':
        $cacheKey = "api_stats_" . date("Y-m-d");
        $cached = $redis ? $redis->get($cacheKey) : null;
        
        if ($cached) {
            echo $cached;
            break;
        }
        
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_calls,
                SUM(duration) as total_duration,
                ROUND(AVG(duration)) as avg_duration,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'no_answer' THEN 1 ELSE 0 END) as no_answer,
                SUM(CASE WHEN status = 'busy' THEN 1 ELSE 0 END) as busy
            FROM calls 
            WHERE DATE(created_at) = CURDATE()
        ");
        
        $response = json_encode(['date' => date('Y-m-d'), 'stats' => $stmt->fetch()]);
        
        if ($redis) {
            $redis->setex($cacheKey, 60, $response);
        }
        
        echo $response;
        break;
        
    case 'calls':
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        $stmt = $pdo->prepare("SELECT * FROM calls ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $total = $pdo->query("SELECT COUNT(*) FROM calls")->fetchColumn();
        
        echo json_encode([
            'calls' => $stmt->fetchAll(),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total),
                'pages' => ceil($total / $limit)
            ]
        ]);
        break;
        
    case 'webhook':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            break;
        }
        
        $required = ['caller_number', 'destination_number', 'status'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing: $field"]);
                break 2;
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO calls (caller_number, destination_number, duration, status)
            VALUES (:caller, :dest, :duration, :status)
        ");
        
        $stmt->execute([
            ':caller' => $payload['caller_number'],
            ':dest' => $payload['destination_number'],
            ':duration' => intval($payload['duration'] ?? 0),
            ':status' => $payload['status']
        ]);
        
        if ($redis) {
            $redis->del("api_stats_" . date("Y-m-d"));
            $redis->del("call_stats_" . date("Y-m-d"));
        }
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action', 'actions' => ['health', 'stats', 'calls', 'webhook']]);
}