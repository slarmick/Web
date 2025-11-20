<?php
class RedisService {
    private $storagePath;
    public $isConnected = false;

    public function __construct() {
        $this->storagePath = __DIR__ . '/redis_data/';
        
        // Создаем директорию для хранения данных, если её нет
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
        
        $this->isConnected = true; // Всегда "подключено" для файловой системы
    }

    // Кэширование данных о мастер-классах
    public function cacheRegistration($registrationId, $data) {
        $key = "registration:{$registrationId}";
        return $this->setex($key, 3600, json_encode($data));
    }

    public function getCachedRegistration($registrationId) {
        $key = "registration:{$registrationId}";
        $data = $this->get($key);
        return $data ? json_decode($data, true) : null;
    }

    // Статистика в реальном времени
    public function incrementStats($topic, $format) {
        $this->hIncrBy("stats:topics", $topic, 1);
        $this->hIncrBy("stats:formats", $format, 1);
        $this->incr("stats:total_registrations");
        
        return true;
    }

    public function getRealTimeStats() {
        return [
            'topics' => $this->hGetAll("stats:topics"),
            'formats' => $this->hGetAll("stats:formats"),
            'total' => $this->get("stats:total_registrations") ?: 0
        ];
    }

    // Сессии пользователей
    public function storeUserSession($sessionId, $userData) {
        return $this->setex("session:{$sessionId}", 7200, json_encode($userData));
    }

    public function getUserSession($sessionId) {
        $data = $this->get("session:{$sessionId}");
        return $data ? json_decode($data, true) : null;
    }

    // Эмуляция Redis методов через файловую систему
    
    private function setex($key, $expire, $value) {
        $filename = $this->getFilename($key);
        $data = [
            'value' => $value,
            'expire' => time() + $expire
        ];
        return file_put_contents($filename, json_encode($data)) !== false;
    }

    private function get($key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($filename), true);
        
        // Проверяем expiration
        if (isset($data['expire']) && time() > $data['expire']) {
            unlink($filename);
            return null;
        }
        
        return $data['value'] ?? null;
    }

    private function hIncrBy($hashKey, $field, $increment) {
        $hash = $this->hGetAll($hashKey);
        $hash[$field] = ($hash[$field] ?? 0) + $increment;
        $this->hSetAll($hashKey, $hash);
    }

    private function hGetAll($hashKey) {
        $filename = $this->getFilename($hashKey);
        
        if (!file_exists($filename)) {
            return [];
        }
        
        $data = json_decode(file_get_contents($filename), true);
        
        // Проверяем expiration
        if (isset($data['expire']) && time() > $data['expire']) {
            unlink($filename);
            return [];
        }
        
        return $data['value'] ?? [];
    }

    private function hSetAll($hashKey, $hash) {
        $filename = $this->getFilename($hashKey);
        $data = [
            'value' => $hash,
            'expire' => time() + 86400 // 24 часа для статистики
        ];
        return file_put_contents($filename, json_encode($data)) !== false;
    }

    private function incr($key) {
        $value = $this->get($key) ?? 0;
        $value++;
        $this->setex($key, 86400, $value); // 24 часа
        return $value;
    }

    private function getFilename($key) {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->storagePath . $safeKey . '.json';
    }

    // Очистка устаревших данных (можно вызывать периодически)
    public function cleanupExpired() {
        $files = glob($this->storagePath . '*.json');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (isset($data['expire']) && time() > $data['expire']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}
?>