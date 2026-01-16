<?php
/**
 * Company X Configuration
 * 
 * All settings are loaded from environment variables with sensible defaults.
 */

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'talkmetrics');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Redis configuration
define('REDIS_HOST', getenv('REDIS_HOST') ?: 'localhost');
define('REDIS_PORT', getenv('REDIS_PORT') ?: 6379);

// Initialize Redis connection
$redis = null;
try {
    $redis = new Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT);
    $redis->ping();
} catch (Exception $e) {
    $redis = null;
    error_log("Redis connection failed: " . $e->getMessage());
}