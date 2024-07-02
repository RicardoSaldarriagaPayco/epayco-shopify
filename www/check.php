<?php
try {
    $redis = new Redis();
    $redis->connect('manage-redis-standalone.thdfos.0001.use1.cache.amazonaws.com', 6379);
    echo "Connection to Redis server successful.\n";
} catch (Exception $e) {
    echo "Could not connect to Redis: " . $e->getMessage() . "\n";
}
?>