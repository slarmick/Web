public function publishToKafka($data, $topicType = 'main') {
    try {
        // Включаем буферизацию вывода
        ob_start();
        
        $topic = $topicType === 'error' ? $this->errorTopic : $this->mainTopic;
        $config = ProducerConfig::getInstance();
        $config->setMetadataBrokerList('kafka:9092');
        $config->setRequiredAck(1);
        
        $producer = new Producer(function() use ($data, $topic) {
            return [[
                'topic' => $topic,
                'value' => json_encode($data),
                'key' => uniqid(),
            ]];
        });
        
        $producer->send(true);
        
        // Очищаем буфер
        ob_end_clean();
        
        error_log("📤 Kafka: отправлено в топик {$topic}");
        return true;
    } catch (Exception $e) {
        // Очищаем буфер даже при ошибке
        ob_end_clean();
        error_log("❌ Kafka ошибка: " . $e->getMessage());
        return false;
    }
}