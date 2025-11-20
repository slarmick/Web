<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналитика регистраций - Лабораторная 6</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #3498db;
        }
        .connection-status {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-connected {
            background: #d4edda;
            color: #155724;
        }
        .status-disconnected {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Аналитика регистраций - NoSQL интеграция</h1>
        
        <?php
        require_once 'Lab6Controller.php';
        $controller = new Lab6Controller();
        $status = $controller->getConnectionStatus();
        $stats = $controller->getComprehensiveStats();
        ?>
        
        <div class="connection-status">
            <h3>Статус подключений:</h3>
            <?php foreach($status as $db => $isConnected): ?>
                <span class="status-badge <?= $isConnected ? 'status-connected' : 'status-disconnected' ?>">
                    <?= strtoupper($db) ?>: <?= $isConnected ? '✅ Подключено' : '❌ Отключено' ?>
                </span>
            <?php endforeach; ?>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>🎯 Реальная статистика (Redis)</h3>
                <p><strong>Всего регистраций:</strong> <?= $stats['real_time']['total'] ?? 0 ?></p>
                <h4>Популярные темы:</h4>
                <?php foreach(($stats['real_time']['topics'] ?? []) as $topic => $count): ?>
                    <p><?= $topic ?>: <?= $count ?></p>
                <?php endforeach; ?>
            </div>

            <div class="stat-card">
                <h3>📈 Аналитика форматов (Elasticsearch)</h3>
                <?php if(isset($stats['analytics']['formats'])): ?>
                    <?php foreach($stats['analytics']['formats']['buckets'] ?? [] as $bucket): ?>
                        <p><?= $bucket['key'] ?>: <?= $bucket['doc_count'] ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <h3>🏆 Топ тем (ClickHouse)</h3>
                <?php foreach(($stats['popular_topics'] ?? []) as $topic): ?>
                    <p><?= $topic['topic'] ?>: <?= $topic['registrations_count'] ?> (<?= $topic['percentage'] ?>%)</p>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Дополнительные графики и таблицы можно добавить с помощью Chart.js -->
    </div>
</body>
</html>