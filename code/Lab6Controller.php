<?php
require_once 'RedisService.php';
require_once 'ElasticsearchService.php';
require_once 'ClickHouseService.php';

class Lab6Controller {
    private $redis;
    private $elasticsearch;
    private $clickhouse;

    public function __construct() {
        $this->redis = new RedisService();
        $this->elasticsearch = new ElasticsearchService();
        $this->clickhouse = new ClickHouseService();
        
        // Инициализируем индексы при первом запуске
        $this->elasticsearch->createIndex();
    }

    // Обработка новой регистрации
    public function processRegistration($formData) {
        $registrationId = uniqid('reg_', true);
        
        // 1. Кэшируем в Redis
        $this->redis->cacheRegistration($registrationId, $formData);
        
        // 2. Индексируем в Elasticsearch для поиска
        $this->elasticsearch->indexRegistration($formData);
        
        // 3. Логируем в ClickHouse для аналитики
        $this->clickhouse->logRegistration($formData);
        
        // 4. Обновляем статистику в реальном времени
        $this->redis->incrementStats($formData['topic'], $formData['format']);
        
        return $registrationId;
    }

    // Получение комплексной статистики
    public function getComprehensiveStats() {
        return [
            'real_time' => $this->redis->getRealTimeStats(),
            'analytics' => $this->elasticsearch->getTopicAnalytics(),
            'aggregated' => $this->clickhouse->getAggregatedStats(30),
            'popular_topics' => $this->clickhouse->getPopularTopics(5),
            'daily_trends' => $this->clickhouse->getDailyRegistrations(7)
        ];
    }

    // Поиск по регистрациям
    public function searchRegistrations($query) {
        return $this->elasticsearch->searchRegistrations($query);
    }

    // Проверка статусов подключений
    public function getConnectionStatus() {
        return [
            'redis' => $this->redis->isConnected,
            'elasticsearch' => $this->elasticsearch->isConnected,
            'clickhouse' => $this->clickhouse->isConnected
        ];
    }
}
?>