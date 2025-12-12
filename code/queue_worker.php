<?php
// queue_worker.php - Worker для обработки сообщений из очередей
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/QueueManager.php';

class QueueWorker {
    private $queueManager;
    private $processedCount = 0;
    private $errorCount = 0;

    public function __construct() {
        $this->queueManager = new QueueManager();
        echo "🚀 Queue Worker запущен...\n";
        echo "📊 Ожидание сообщений...\n\n";
    }

    public function start() {
        // В этой версии просто выводим статистику
        // В реальном приложении здесь был бы цикл обработки сообщений
        echo "✅ Worker готов к работе\n";
        echo "📈 Текущая статистика:\n";
        
        $stats = $this->queueManager->getQueueStats();
        echo "   RabbitMQ: " . ($stats['rabbitmq']['connected'] ? '✅' : '❌') . "\n";
        echo "   Kafka: " . ($stats['kafka']['connected'] ? '✅' : '❌') . "\n";
        echo "   Сообщений в Kafka: " . $stats['kafka']['messages_sent'] . "\n\n";
        
        echo "🏁 Worker завершил работу (демо-режим)\n";
        echo "⚠️ Для реальной обработки требуется RabbitMQ Consumer\n";
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