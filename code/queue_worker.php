<?php
require_once 'QueueManager.php';

class QueueWorker {
    private $queueManager;
    private $processedCount = 0;
    private $errorCount = 0;

    public function __construct() {
        $this->queueManager = new QueueManager();
        echo "🚀 Queue Worker запущен...\n";
        echo "📊 Ожидание сообщений из RabbitMQ и Kafka...\n\n";
    }

    public function processMessage($data, $queueType) {
        try {
            echo "🔧 Обработка сообщения из {$queueType}:\n";
            echo "   👤 Имя: " . ($data['name'] ?? 'N/A') . "\n";
            echo "   📧 Email: " . ($data['email'] ?? 'N/A') . "\n";
            echo "   🎯 Тема: " . ($data['topic'] ?? 'N/A') . "\n";
            
            // Имитация обработки
            sleep(1);
            
            // Случайная "ошибка" для демонстрации (10% случаев)
            if (rand(1, 10) === 1) {
                throw new Exception("Случайная ошибка обработки");
            }
            
            // Сохраняем в лог
            $logEntry = [
                'processed_at' => date('Y-m-d H:i:s'),
                'source' => $queueType,
                'data' => $data,
                'status' => 'success'
            ];
            
            file_put_contents('queue_processed.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND);
            
            $this->processedCount++;
            echo "   ✅ Успешно обработано (всего: {$this->processedCount})\n\n";
            
        } catch (Exception $e) {
            $this->errorCount++;
            echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
            echo "   📨 Отправка в очередь ошибок...\n";
            
            // Отправляем в очередь ошибок
            $errorData = [
                'original_data' => $data,
                'error_message' => $e->getMessage(),
                'failed_at' => date('Y-m-d H:i:s'),
                'source' => $queueType
            ];
            
            if ($queueType === 'rabbitmq') {
                $this->queueManager->publishToRabbitMQ($errorData, 'error');
            } else {
                $this->queueManager->publishToKafka($errorData, 'error');
            }
            
            echo "   📊 Ошибок всего: {$this->errorCount}\n\n";
        }
    }

    public function start() {
        // Запускаем обработчики в фоне
        $this->startRabbitWorker();
        $this->startKafkaWorker();
    }

    private function startRabbitWorker() {
        // Основная очередь
        pcntl_fork(); // Создаем дочерний процесс
        
        $this->queueManager->consumeRabbitMQ('main', [$this, 'processMessage']);
    }

    private function startKafkaWorker() {
        // Основной топик  
        pcntl_fork(); // Создаем дочерний процесс
        
        $this->queueManager->consumeKafka('main', [$this, 'processMessage']);
    }
}

// Запуск воркера
if (php_sapi_name() === 'cli') {
    $worker = new QueueWorker();
    $worker->start();
} else {
    echo "🚫 Этот скрипт должен запускаться из командной строки\n";
}
?>