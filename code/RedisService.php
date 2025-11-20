<?php
class RedisService {
    private $redis;
    private $isConnected = false;

    public function __construct() {
        try {
            $this->redis = new Redis();
            $this->redis->connect('redis', 6379, 2);
            $this->redis->ping();
            $this->isConnected = true;
        } catch (Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            $this->isConnected = false;
        }
    }

    // Кэширование данных о мастер-классах
    public function cacheRegistration($registrationId, $data) {
        if (!$this->isConnected) return false;
        
        $key = "registration:{$registrationId}";
        return $this->redis->setex($key, 3600, json_encode($data));
    }

    public function getCachedRegistration($registrationId) {
        if (!$this->isConnected) return null;
        
        $key = "registration:{$registrationId}";
        $data = $this->redis->get($key);
        return $data ? json_decode($data, true) : null;
    }

    // Статистика в реальном времени
    public function incrementStats($topic, $format) {
        if (!$this->isConnected) return false;
        
        $this->redis->hIncrBy("stats:topics", $topic, 1);
        $this->redis->hIncrBy("stats:formats", $format, 1);
        $this->redis->incr("stats:total_registrations");
        
        return true;
    }

    public function getRealTimeStats() {
        if (!$this->isConnected) return null;
        
        return [
            'topics' => $this->redis->hGetAll("stats:topics") ?: [],
            'formats' => $this->redis->hGetAll("stats:formats") ?: [],
            'total' => $this->redis->get("stats:total_registrations") ?: 0
        ];
    }

    // Сессии пользователей
    public function storeUserSession($sessionId, $userData) {
        if (!$this->isConnected) return false;
        
        return $this->redis->setex("session:{$sessionId}", 7200, json_encode($userData));
    }

    public function getUserSession($sessionId) {
        if (!$this->isConnected) return null;
        
        $data = $this->redis->get("session:{$sessionId}");
        return $data ? json_decode($data, true) : null;
    }
}
?>