<?php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Kafka\Producer;
use Kafka\ProducerConfig;
use Kafka\Consumer;
use Kafka\ConsumerConfig;

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

    // 📤 ОТПРАВКА СООБЩЕНИЙ

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

    public function publishToKafka($data, $topicType = 'main') {
        try {
            $topic = $topicType === 'error' ? $this->errorTopic : $this->mainTopic;
            
            $config = ProducerConfig::getInstance();
            $config->setMetadataBrokerList('kafka:9092');
            $config->setRequiredAck(1);
            $config->setIsAsyn(false);
            $config->setProduceInterval(500);

            $producer = new Producer(function() use ($data, $topic) {
                return [[
                    'topic' => $topic,
                    'value' => json_encode($data),
                    'key' => uniqid(),
                ]];
            });

            $producer->send(true);
            error_log("📤 Kafka: Сообщение отправлено в топик {$topic}");
            return true;
        } catch (Exception $e) {
            error_log("❌ Ошибка отправки в Kafka: " . $e->getMessage());
            return false;
        }
    }

    // 📥 ПОЛУЧЕНИЕ СООБЩЕНИЙ

    public function consumeRabbitMQ($queueType = 'main', callable $callback) {
        if (!$this->rabbitChannel) return;

        $queue = $queueType === 'error' ? $this->errorQueue : $this->mainQueue;
        
        echo "👷 RabbitMQ Worker запущен для очереди: {$queue}\n";

        $this->rabbitChannel->basic_consume($queue, '', false, true, false, false, 
            function($msg) use ($callback) {
                try {
                    $data = json_decode($msg->body, true);
                    echo "📥 RabbitMQ: Получено сообщение\n";
                    $callback($data, 'rabbitmq');
                } catch (Exception $e) {
                    error_log("❌ Ошибка обработки RabbitMQ сообщения: " . $e->getMessage());
                }
            }
        );

        while ($this->rabbitChannel->is_consuming()) {
            $this->rabbitChannel->wait();
        }
    }

    public function consumeKafka($topicType = 'main', callable $callback) {
        try {
            $topic = $topicType === 'error' ? $this->errorTopic : $this->mainTopic;
            
            $config = ConsumerConfig::getInstance();
            $config->setMetadataBrokerList('kafka:9092');
            $config->setGroupId('lab7_group');
            $config->setTopics([$topic]);
            $config->setOffsetReset('earliest');

            $consumer = new Consumer();
            
            echo "👷 Kafka Worker запущен для топика: {$topic}\n";

            $consumer->start(function($topic, $part, $message) use ($callback) {
                try {
                    $data = json_decode($message['message']['value'], true);
                    echo "📥 Kafka: Получено сообщение\n";
                    $callback($data, 'kafka');
                } catch (Exception $e) {
                    error_log("❌ Ошибка обработки Kafka сообщения: " . $e->getMessage());
                }
            });
        } catch (Exception $e) {
            error_log("❌ Ошибка Kafka consumer: " . $e->getMessage());
        }
    }

    // 📊 СТАТИСТИКА

    public function getQueueStats() {
        $stats = [
            'rabbitmq' => [
                'main_queue' => 0,
                'error_queue' => 0,
                'connected' => (bool)$this->rabbitChannel
            ],
            'kafka' => [
                'main_topic' => 'N/A',
                'error_topic' => 'N/A', 
                'connected' => true
            ]
        ];

        // Получаем статистику RabbitMQ (упрощенно)
        if ($this->rabbitChannel) {
            try {
                $mainQueueInfo = $this->rabbitChannel->queue_declare($this->mainQueue, true);
                $errorQueueInfo = $this->rabbitChannel->queue_declare($this->errorQueue, true);
                
                $stats['rabbitmq']['main_queue'] = $mainQueueInfo[1] ?? 0;
                $stats['rabbitmq']['error_queue'] = $errorQueueInfo[1] ?? 0;
            } catch (Exception $e) {
                error_log("❌ Ошибка получения статистики RabbitMQ: " . $e->getMessage());
            }
        }

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