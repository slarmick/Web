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
    private $mainQueue = 'lab7_main_queue';
    private $errorQueue = 'lab7_error_queue';
    private $mainTopic = 'lab7_main_topic';
    private $errorTopic = 'lab7_error_topic';

    public function __construct() {
        $this->initRabbitMQ();
    }

    private function initRabbitMQ() {
        try {
            $this->rabbitConnection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
            $this->rabbitChannel = $this->rabbitConnection->channel();
            $this->rabbitChannel->queue_declare($this->mainQueue, false, true, false, false);
            $this->rabbitChannel->queue_declare($this->errorQueue, false, true, false, false);
            error_log("✅ RabbitMQ подключен");
        } catch (Exception $e) {
            error_log("❌ RabbitMQ: " . $e->getMessage());
            $this->rabbitChannel = null;
        }
    }

    public function publishToRabbitMQ($data, $queueType = 'main') {
        if (!$this->rabbitChannel) return false;
        try {
            $queue = $queueType === 'error' ? $this->errorQueue : $this->mainQueue;
            $msg = new AMQPMessage(json_encode($data), ['delivery_mode' => 2]);
            $this->rabbitChannel->basic_publish($msg, '', $queue);
            error_log("📤 RabbitMQ: отправлено в {$queue}");
            return true;
        } catch (Exception $e) {
            error_log("❌ RabbitMQ ошибка: " . $e->getMessage());
            return false;
        }
    }

    public function publishToKafka($data, $topicType = 'main') {
        try {
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
            error_log("📤 Kafka: отправлено в топик {$topic}");
            return true;
        } catch (Exception $e) {
            error_log("❌ Kafka ошибка: " . $e->getMessage());
            return false;
        }
    }

    public function getQueueStats() {
        $stats = [
            'rabbitmq' => [
                'main_queue' => 0,
                'error_queue' => 0,
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
}
?>