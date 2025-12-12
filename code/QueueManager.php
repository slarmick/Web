<?php
// QueueManager.php - исправленная версия с Kafka

// Включаем автозагрузчик в начале файла
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Kafka\Producer;
use Kafka\ProducerConfig;

class QueueManager {
    private $rabbitChannel;
    private $rabbitConnection;
    
    // Очереди RabbitMQ
    private $mainQueue = 'lab7_main_queue';
    private $errorQueue = 'lab7_error_queue';
    
    // Топики Kafka
    private $mainTopic = 'lab7_main_topic';
    private $errorTopic = 'lab7_error_topic';

    public function __construct() {
        $this->initRabbitMQ();
    }

    private function initRabbitMQ() {
        try {
            $this->rabbitConnection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
            $this->rabbitChannel = $this->rabbitConnection->channel();
            
            // Объявляем основные очереди
            $this->rabbitChannel->queue_declare($this->mainQueue, false, true, false, false);
            $this->rabbitChannel->queue_declare($this->errorQueue, false, true, false, false);
            
            error_log("✅ RabbitMQ подключен успешно");
        } catch (Exception $e) {
            error_log("❌ Ошибка подключения RabbitMQ: " . $e->getMessage());
            $this->rabbitChannel = null;
        }
    }

    // 📤 ОТПРАВКА В RABBITMQ
    public function publishToRabbitMQ($data, $queueType = 'main') {
        if (!$this->rabbitChannel) return false;

        try {
            $queue = $queueType === 'error' ? $this->errorQueue : $this->mainQueue;
            $msg = new AMQPMessage(json_encode($data), ['delivery_mode' => 2]);
            $this->rabbitChannel->basic_publish($msg, '', $queue);
            
            error_log("📤 RabbitMQ: Сообщение отправлено в очередь {$queue}");
            return true;
        } catch (Exception $e) {
            error_log("❌ Ошибка отправки в RabbitMQ: " . $e->getMessage());
            return false;
        }
    }

    // 📤 ОТПРАВКА В KAFKA (с исправлением deprecated warnings)
    public function publishToKafka($data, $topicType = 'main') {
        // Сохраняем текущий уровень error reporting
        $oldErrorLevel = error_reporting();
        error_reporting($oldErrorLevel & ~E_DEPRECATED);
        
        try {
            $topic = $topicType === 'error' ? $this->errorTopic : $this->mainTopic;
            
            // Включаем буферизацию вывода
            ob_start();
            
            $config = ProducerConfig::getInstance();
            $config->setMetadataBrokerList('kafka:9092');
            $config->setRequiredAck(1);
            $config->setIsAsyn(false);
            $config->setProduceInterval(500);

            $producer = new Producer();
            $producer->setLogger(null); // Отключаем логирование в библиотеке
            
            $result = $producer->send([
                [
                    'topic' => $topic,
                    'value' => json_encode($data),
                    'key' => uniqid(),
                ]
            ]);

            // Очищаем буфер
            $output = ob_get_clean();
            if (!empty($output)) {
                error_log("📤 Kafka output buffered: " . substr($output, 0, 100));
            }
            
            // Восстанавливаем error reporting
            error_reporting($oldErrorLevel);
            
            error_log("📤 Kafka: Сообщение отправлено в топик {$topic}");
            return true;
            
        } catch (Exception $e) {
            // Очищаем буфер даже при ошибке
            ob_end_clean();
            
            // Восстанавливаем error reporting
            error_reporting($oldErrorLevel);
            
            error_log("❌ Ошибка отправки в Kafka: " . $e->getMessage());
            return false;
        }
    }

    // 📊 СТАТИСТИКА (упрощённая)
    public function getQueueStats() {
        $stats = [
            'rabbitmq' => [
                'main_queue' => 'lab7_main_queue',
                'error_queue' => 'lab7_error_queue',
                'connected' => (bool)$this->rabbitChannel
            ],
            'kafka' => [
                'main_topic' => 'lab7_main_topic',
                'error_topic' => 'lab7_error_topic', 
                'connected' => true
            ]
        ];

        return $stats;
    }

    public function __destruct() {
        if ($this->rabbitChannel) {
            $this->rabbitChannel->close();
        }
        if ($this->rabbitConnection) {
            $this->rabbitConnection->close();
        }
    }
}
?>