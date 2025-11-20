<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redis Dashboard - Лабораторная 6</title>
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
        h1 { 
            color: #2c3e50; 
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .nav-buttons {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        .nav-button {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 2px solid #3498db;
        }
        .nav-button:hover {
            background: white;
            color: #3498db;
            transform: translateY(-2px);
        }
        .status-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .status-connected {
            border-left-color: #27ae60;
            background: #d4edda;
        }
        .status-disconnected {
            border-left-color: #e74c3c;
            background: #f8d7da;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        .redis-key {
            background: #2c3e50;
            color: white;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔴 Redis Dashboard - Лабораторная 6</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/view.php" class="nav-button">📊 Все записи</a>
        </div>

        <?php
        require_once 'Lab6Controller.php';
        $controller = new Lab6Controller();
        $status = $controller->getConnectionStatus();
        $stats = $controller->getComprehensiveStats();
        ?>

        <div class="status-card <?= $status['redis'] ? 'status-connected' : 'status-disconnected' ?>">
            <h3>🔌 Статус подключения Redis</h3>
            <p><strong>Статус:</strong> <?= $status['redis'] ? '✅ Подключено' : '❌ Отключено' ?></p>
            <p><strong>Хост:</strong> redis:6379</p>
            <p><strong>Всего ключей:</strong> <?= count($stats['redis_keys'] ?? []) ?></p>
        </div>

        <?php if ($status['redis']): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📊 Реальная статистика</h3>
                    <p><strong>Всего регистраций:</strong> <?= $stats['real_time']['total'] ?? 0 ?></p>
                    
                    <h4>📈 Популярные темы:</h4>
                    <?php foreach(($stats['real_time']['topics'] ?? []) as $topic => $count): ?>
                        <p>• <?= htmlspecialchars($topic) ?>: <?= $count ?></p>
                    <?php endforeach; ?>
                    
                    <h4>🎯 Форматы участия:</h4>
                    <?php foreach(($stats['real_time']['formats'] ?? []) as $format => $count): ?>
                        <p>• <?= htmlspecialchars($format) ?>: <?= $count ?></p>
                    <?php endforeach; ?>
                </div>

                <div class="stat-card">
                    <h3>🔑 Ключи в Redis</h3>
                    <?php foreach(($stats['redis_keys'] ?? []) as $key): ?>
                        <div class="redis-key"><?= htmlspecialchars($key) ?></div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($stats['redis_keys'])): ?>
                        <p>⏳ Ключей пока нет. Заполните форму регистрации.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card">
                <h3>🚀 Тестирование Redis</h3>
                <p>Redis успешно интегрирован в систему регистрации. Каждая новая регистрация:</p>
                <ul>
                    <li>✅ Кэшируется в Redis на 1 час</li>
                    <li>📊 Обновляет статистику в реальном времени</li>
                    <li>💾 Сохраняет сессию пользователя</li>
                </ul>
            </div>

        <?php else: ?>
            <div class="stat-card">
                <h3>⚠️ Redis недоступен</h3>
                <p>Для включения Redis выполните:</p>
                <pre>docker-compose up -d redis</pre>
                <p>Система продолжит работать с MySQL и файловым хранилищем.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>