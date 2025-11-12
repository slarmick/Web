<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Test - Лабораторные работы</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .nav-buttons {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .nav-button {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
        }
        .test-result {
            background: #d4edda;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 PHP Test Page</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/info.php" class="nav-button">⚙️ PHP Info</a>
        </div>

        <div class="test-result">
            <h3>✅ PHP работает корректно!</h3>
            <p><strong>Версия PHP:</strong> <?= phpversion() ?></p>
            <p><strong>Время сервера:</strong> <?= date('Y-m-d H:i:s') ?></p>
            <p><strong>Сессия:</strong> <?= session_id() ?></p>
        </div>

        <?php
        // Тест различных функций PHP
        $tests = [
            'MySQL PDO' => extension_loaded('pdo_mysql'),
            'Redis' => extension_loaded('redis'),
            'JSON' => function_exists('json_encode'),
            'cURL' => function_exists('curl_init'),
            'GD Library' => function_exists('imagecreate'),
        ];
        ?>

        <h3>🔧 Проверка расширений PHP:</h3>
        <ul>
            <?php foreach($tests as $name => $result): ?>
                <li><?= $name ?>: <?= $result ? '✅' : '❌' ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>