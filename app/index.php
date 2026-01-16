<?php
require_once 'config.php';

$pageTitle = "Company X Dashboard";
$error = null;
$calls = [];
$stats = null;
$cacheStatus = "miss";

try {
    $cacheKey = "call_stats_" . date("Y-m-d");
    $cachedStats = null;
    
    if ($redis) {
        $cachedStats = $redis->get($cacheKey);
        if ($cachedStats) {
            $stats = json_decode($cachedStats, true);
            $cacheStatus = "hit";
        }
    }
    
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    $stmt = $pdo->query("SELECT * FROM calls ORDER BY created_at DESC LIMIT 20");
    $calls = $stmt->fetchAll();
    
    if (!$stats) {
        $statsQuery = $pdo->query("
            SELECT 
                COUNT(*) as total_calls,
                SUM(duration) as total_duration,
                AVG(duration) as avg_duration,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_calls,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_calls
            FROM calls 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stats = $statsQuery->fetch();
        
        if ($redis) {
            $redis->setex($cacheKey, 300, json_encode($stats));
        }
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

function formatDuration($seconds) {
    if ($seconds < 60) return $seconds . "s";
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return $mins . "m " . $secs . "s";
}

function getStatusBadge($status) {
    $colors = [
        'completed' => '#10b981',
        'failed' => '#ef4444',
        'no_answer' => '#f59e0b',
        'busy' => '#6b7280'
    ];
    $color = $colors[$status] ?? '#6b7280';
    return "<span class='badge' style='background:{$color}'>{$status}</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <span class="cache-status">Cache: <?= $cacheStatus ?></span>
        </header>
        
        <?php if ($error): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            
            <section class="stats-grid">
                <div class="stat-card">
                    <h3>Total Calls Today</h3>
                    <p class="stat-value"><?= number_format($stats['total_calls'] ?? 0) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Completed</h3>
                    <p class="stat-value success"><?= number_format($stats['completed_calls'] ?? 0) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Failed</h3>
                    <p class="stat-value danger"><?= number_format($stats['failed_calls'] ?? 0) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Avg Duration</h3>
                    <p class="stat-value"><?= formatDuration(round($stats['avg_duration'] ?? 0)) ?></p>
                </div>
            </section>
            
            <section class="recent-calls">
                <h2>Recent Calls</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Caller</th>
                            <th>Destination</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($calls as $call): ?>
                        <tr>
                            <td><?= htmlspecialchars($call['id']) ?></td>
                            <td><?= htmlspecialchars($call['caller_number']) ?></td>
                            <td><?= htmlspecialchars($call['destination_number']) ?></td>
                            <td><?= formatDuration($call['duration']) ?></td>
                            <td><?= getStatusBadge($call['status']) ?></td>
                            <td><?= htmlspecialchars($call['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            
        <?php endif; ?>
        
        <footer>
            <p>Server: <?= gethostname() ?> | PHP <?= phpversion() ?> | <?= date('Y-m-d H:i:s') ?></p>
        </footer>
    </div>
</body>
</html>