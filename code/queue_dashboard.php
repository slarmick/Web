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
        .message-item { background: #34495e; margin: 5px 0; padding: 10px; border-radius: 3px; }
        .status-good { color: #27ae60; font-weight: bold; }
        .status-bad { color: #e74c3c; font-weight: bold; }
        .refresh-btn { background: #2ecc71; border-color: #2ecc71; }
        .refresh-btn:hover { background: white; color: #2ecc71; }
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
        require_once __DIR__ . '/vendor/autoload.php';
        
        try {
            if (!file_exists(__DIR__ . '/QueueManager.php')) {
                throw new Exception("QueueManager.php не найден");
            }
            
            require_once __DIR__ . '/QueueManager.php';
            $queueManager = new QueueManager();
            $stats = $queueManager->getQueueStats();
            
            // Получаем последние сообщения Kafka
            $kafkaMessages = $queueManager->getKafkaMessages(10);
            
        } catch (Exception $e) {
            echo "<div class='stat-card' style='border-left-color: #e74c3c;'>";
            echo "<h3>❌ Ошибка подключения</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            $stats = null;
            $kafkaMessages = [];
        }
        ?>

        <?php if ($stats): ?>
        <div class="stats-grid">
            <div class="stat-card rabbit-card">
                <h3>🐇 RabbitMQ Status</h3>
                <p><strong>Статус:</strong> 
                    <span class="<?= $stats['rabbitmq']['connected'] ? 'status-good' : 'status-bad' ?>">
                        <?= $stats['rabbitmq']['status'] ?>
                    </span>
                </p>
                <p><strong>Основная очередь:</strong> <?= htmlspecialchars($stats['rabbitmq']['main_queue']) ?></p>
                <p><strong>Очередь ошибок:</strong> <?= htmlspecialchars($stats['rabbitmq']['error_queue']) ?></p>
                <p><strong>Сообщений:</strong> <?= htmlspecialchars($stats['rabbitmq']['messages_sent']) ?></p>
                <p><strong>Админка:</strong> <a href="http://localhost:15672" target="_blank">http://localhost:15672</a></p>
            </div>

            <div class="stat-card kafka-card">
                <h3>🦊 Kafka Status</h3>
                <p><strong>Статус:</strong> 
                    <span class="<?= $stats['kafka']['connected'] ? 'status-good' : 'status-bad' ?>">
                        <?= $stats['kafka']['status'] ?>
                    </span>
                </p>
                <p><strong>Основной топик:</strong> <?= htmlspecialchars($stats['kafka']['main_topic']) ?></p>
                <p><strong>Топик ошибок:</strong> <?= htmlspecialchars($stats['kafka']['error_topic']) ?></p>
                <p><strong>Сообщений отправлено:</strong> <?= $stats['kafka']['messages_sent'] ?></p>
                <p><strong>Broker:</strong> kafka:9092</p>
            </div>
        </div>

        <div class="stat-card">
            <h3>📝 Последние сообщения Kafka</h3>
            <div class="log-container">
                <?php if (!empty($kafkaMessages)): ?>
                    <?php foreach ($kafkaMessages as $message): ?>
                        <div class="message-item">
                            <strong><?= htmlspecialchars($message['timestamp'] ?? 'N/A') ?></strong><br>
                            Топик: <?= htmlspecialchars($message['topic'] ?? 'N/A') ?><br>
                            Данные: <?= htmlspecialchars(json_encode($message['data'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Сообщений пока нет. Заполните форму регистрации.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <div class="stat-card">
            <h3>⚠️ Ошибка загрузки статистики</h3>
            <p>Проверьте наличие файла QueueManager.php и правильность его работы.</p>
            <p>Попробуйте заполнить форму регистрации сначала: 
                <a href="/master-class.html">Форма регистрации</a>
            </p>
        </div>
        <?php endif; ?>

        <div class="nav-buttons">
            <button onclick="location.reload()" class="nav-button refresh-btn">🔄 Обновить</button>
            <button onclick="alert('Запуск worker: docker exec -it lab7_php php queue_worker.php')" class="nav-button">
                👷 Запустить Worker
            </button>
            <a href="/master-class.html" class="nav-button">📝 Отправить тестовое сообщение</a>
        </div>
    </div>
</body>
</html>