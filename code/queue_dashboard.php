<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Dashboard - Лабораторная 7</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin: 40px 0;
        }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #3498db; }
        .rabbit-card { border-left-color: #FF6600; }
        .kafka-card { border-left-color: #000000; }
        .nav-buttons { display: flex; gap: 15px; margin: 30px 0; flex-wrap: wrap; justify-content: center; }
        .nav-button { background: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold; transition: all 0.3s ease; border: 2px solid #3498db; }
        .nav-button:hover { background: white; color: #3498db; transform: translateY(-2px); }
        .log-container { background: #2c3e50; color: white; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Queue Dashboard - Лабораторная 7</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/view.php" class="nav-button">📊 Все записи</a>
            <a href="http://localhost:15672" target="_blank" class="nav-button">🐇 RabbitMQ Admin</a>
        </div>

        <?php
        require_once 'QueueManager.php';
        $queueManager = new QueueManager();
        $stats = $queueManager->getQueueStats();
        ?>

        <div class="stats-grid">
            <div class="stat-card rabbit-card">
                <h3>🐇 RabbitMQ Status</h3>
                <p><strong>Connected:</strong> <?= $stats['rabbitmq']['connected'] ? '✅ Yes' : '❌ No' ?></p>
                <p><strong>Main Queue:</strong> <?= $stats['rabbitmq']['main_queue'] ?> messages</p>
                <p><strong>Error Queue:</strong> <?= $stats['rabbitmq']['error_queue'] ?> messages</p>
                <p><strong>Admin:</strong> <a href="http://localhost:15672" target="_blank">http://localhost:15672</a></p>
            </div>

            <div class="stat-card kafka-card">
                <h3>🦊 Kafka Status</h3>
                <p><strong>Connected:</strong> <?= $stats['kafka']['connected'] ? '✅ Yes' : '❌ No' ?></p>
                <p><strong>Main Topic:</strong> <?= $stats['kafka']['main_topic'] ?></p>
                <p><strong>Error Topic:</strong> <?= $stats['kafka']['error_topic'] ?></p>
                <p><strong>Broker:</strong> localhost:9093</p>
            </div>
        </div>

        <div class="stat-card">
            <h3>📝 Processing Log</h3>
            <div class="log-container">
                <?php
                if (file_exists('queue_processed.log')) {
                    $lines = array_reverse(file('queue_processed.log', FILE_SKIP_EMPTY_LINES));
                    $lines = array_slice($lines, 0, 20); // Последние 20 записей
                    foreach ($lines as $line) {
                        $data = json_decode($line, true);
                        if ($data) {
                            $time = $data['processed_at'] ?? 'N/A';
                            $source = $data['source'] ?? 'N/A';
                            $status = $data['status'] ?? 'N/A';
                            $name = $data['data']['name'] ?? 'N/A';
                            echo "[{$time}] {$source} - {$name} - {$status}\n";
                        }
                    }
                } else {
                    echo "Лог файл не найден. Запустите worker для обработки сообщений.\n";
                }
                ?>
            </div>
        </div>

        <div class="nav-buttons">
            <button onclick="location.reload()" class="nav-button">🔄 Обновить</button>
            <button onclick="alert('Запуск: docker exec -it lab7_php php queue_worker.php')" class="nav-button">
                👷 Запустить Worker
            </button>
        </div>
    </div>
</body>
</html>