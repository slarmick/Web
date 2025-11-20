<?php
class RedisService {
    private $redis;
    public $isConnected = false;

    public function __construct() {
        try {
            $this->redis = new Redis();
            $this->redis->connect('redis', 6379, 2);
            $this->redis->ping();
            $this->isConnected = true;
            error_log("✅ Redis connected successfully");
        } catch (Exception $e) {
            error_log("❌ Redis connection failed: " . $e->getMessage());
            $this->isConnected = false;
        }
    }

    // Кэширование данных о мастер-классах
    public function cacheRegistration($registrationId, $data) {
        if (!$this->isConnected) {
            error_log("Redis not connected, cannot cache registration");
            return false;
        }
        
        $key = "registration:{$registrationId}";
        $result = $this->redis->setex($key, 3600, json_encode($data));
        error_log("Cached registration {$registrationId}: " . ($result ? 'success' : 'failed'));
        return $result;
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
        
        error_log("📊 Updated stats: topic={$topic}, format={$format}");
        return true;
    }

    public function getRealTimeStats() {
        if (!$this->isConnected) return null;
        
        $stats = [
            'topics' => $this->redis->hGetAll("stats:topics") ?: [],
            'formats' => $this->redis->hGetAll("stats:formats") ?: [],
            'total' => $this->redis->get("stats:total_registrations") ?: 0
        ];
        
        return $stats;
    }

    // Сессии пользователей
    public function storeUserSession($sessionId, $userData) {
        if (!$this->isConnected) return false;
        
        $result = $this->redis->setex("session:{$sessionId}", 7200, json_encode($userData));
        error_log("💾 Stored user session {$sessionId}: " . ($result ? 'success' : 'failed'));
        return $result;
    }

    public function getUserSession($sessionId) {
        if (!$this->isConnected) return null;
        
        $data = $this->redis->get("session:{$sessionId}");
        return $data ? json_decode($data, true) : null;
    }

    // Тест подключения
    public function testConnection() {
        if (!$this->isConnected) return false;
        
        try {
            $pong = $this->redis->ping();
            return $pong === true || $pong === '+PONG';
        } catch (Exception $e) {
            error_log("Redis test failed: " . $e->getMessage());
            return false;
        }
    }

    // Получение всех ключей (для отладки)
    public function getAllKeys($pattern = '*') {
        if (!$this->isConnected) return [];
        
        return $this->redis->keys($pattern);
    }
}
?>