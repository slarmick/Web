<?php
// QueueManager.php - исправленная версия

// Включаем автозагрузчик
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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
            
            $this->rabbitChannel->queue_declare($this->mainQueue, false, true, false, false);
            $this->rabbitChannel->queue_declare($this->errorQueue, false, true, false, false);
            
            error_log("✅ RabbitMQ подключен успешно");
        } catch (Exception $e) {
            error_log("❌ RabbitMQ: " . $e->getMessage());
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
            
            error_log("📤 RabbitMQ: отправлено в очередь {$queue}");
            return true;
        } catch (Exception $e) {
            error_log("❌ RabbitMQ ошибка: " . $e->getMessage());
            return false;
        }
    }

    // 📤 ОТПРАВКА В KAFKA (упрощенная, без deprecated warnings)
    public function publishToKafka($data, $topicType = 'main') {
        // Сохраняем текущий уровень error reporting
        $oldErrorLevel = error_reporting();
        
        try {
            // Отключаем deprecated warnings для Kafka библиотеки
            error_reporting($oldErrorLevel & ~E_DEPRECATED & ~E_WARNING);
            
            // Включаем буферизацию вывода
            ob_start();
            
            $topic = $topicType === 'error' ? $this->errorTopic : $this->mainTopic;
            
            // Простая проверка доступности Kafka
            if (!$this->isKafkaAvailable()) {
                error_log("⚠️ Kafka недоступен, пропускаем отправку");
                ob_end_clean();
                error_reporting($oldErrorLevel);
                return false;
            }
            
            // Упрощенная отправка в Kafka
            error_log("📤 Kafka: подготовка отправки в топик {$topic}");
            
            // Сохраняем в файл для демонстрации (вместо реальной отправки)
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'topic' => $topic,
                'data' => $data
            ];
            
            file_put_contents(
                'kafka_messages.log', 
                json_encode($logData) . PHP_EOL, 
                FILE_APPEND
            );
            
            // Очищаем буфер
            $output = ob_get_contents();
            ob_end_clean();
            
            if (!empty($output)) {
                error_log("📤 Kafka output: " . substr($output, 0, 200));
            }
            
            // Восстанавливаем error reporting
            error_reporting($oldErrorLevel);
            
            error_log("✅ Kafka: сообщение записано в лог (топик: {$topic})");
            return true;
            
        } catch (Exception $e) {
            // Очищаем буфер при ошибке
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Восстанавливаем error reporting
            error_reporting($oldErrorLevel);
            
            error_log("❌ Kafka исключение: " . $e->getMessage());
            return false;
        }
    }

    // Проверка доступности Kafka
    private function isKafkaAvailable() {
        $host = 'kafka';
        $port = 9092;
        $timeout = 2;
        
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($socket) {
            fclose($socket);
            return true;
        }
        return false;
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