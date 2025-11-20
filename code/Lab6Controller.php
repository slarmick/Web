<?php
require_once 'RedisService.php';

class Lab6Controller {
    private $redis;

    public function __construct() {
        $this->redis = new RedisService();
        
        // Логируем статус подключения
        if ($this->redis->isConnected) {
            error_log("🚀 LAB6: Redis service initialized successfully");
            $testResult = $this->redis->testConnection();
            error_log("🔍 LAB6: Redis connection test: " . ($testResult ? 'PASS' : 'FAIL'));
        } else {
            error_log("❌ LAB6: Redis service initialization FAILED");
        }
    }

    // Обработка новой регистрации
    public function processRegistration($formData) {
        $registrationId = uniqid('reg_', true);
        
        error_log("🎯 LAB6: Processing registration with ID: " . $registrationId);
        
        // 1. Кэшируем в Redis
        $cacheResult = $this->redis->cacheRegistration($registrationId, $formData);
        error_log("💾 LAB6: Cache result: " . ($cacheResult ? 'success' : 'failed'));
        
        // 2. Обновляем статистику в реальном времени
        $statsResult = $this->redis->incrementStats($formData['topic'], $formData['format']);
        error_log("📊 LAB6: Stats update result: " . ($statsResult ? 'success' : 'failed'));
        
        // 3. Сохраняем сессию пользователя
        $sessionData = [
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'registration_time' => date('Y-m-d H:i:s'),
            'form_data' => $formData
        ];
        $sessionResult = $this->redis->storeUserSession(session_id(), $sessionData);
        error_log("👤 LAB6: Session storage result: " . ($sessionResult ? 'success' : 'failed'));
        
        return $registrationId;
    }

    // Получение комплексной статистики
    public function getComprehensiveStats() {
        $stats = [
            'real_time' => $this->redis->getRealTimeStats(),
            'redis_connected' => $this->redis->isConnected,
            'redis_keys' => $this->redis->getAllKeys()
        ];

        error_log("📈 LAB6: Retrieved comprehensive stats");
        return $stats;
    }

    // Проверка статусов подключений
    public function getConnectionStatus() {
        return [
            'redis' => $this->redis->isConnected
        ];
    }

    // Получение кэшированной регистрации
    public function getCachedRegistration($registrationId) {
        return $this->redis->getCachedRegistration($registrationId);
    }

    // Получение сессии пользователя
    public function getUserSession($sessionId) {
        return $this->redis->getUserSession($sessionId);
    }
}
?>