<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Test - Лабораторные работы</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            max-width: 900px; 
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
        h2 {
            color: #3498db;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 30px;
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
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .status-success {
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .status-error {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .tech-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .tech-item {
            background: #3498db;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 PHP Test Страница</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/about.html" class="nav-button">👨‍💻 О нас</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/info.php" class="nav-button">⚙️ PHP Info</a>
        </div>

        <?php
        echo '<div class="status-success">✅ PHP успешно работает!</div>';
        
        echo '<div class="info-card">';
        echo '<h2>📊 Основная информация</h2>';
        echo '<p><strong>Версия PHP:</strong> ' . phpversion() . '</p>';
        echo '<p><strong>Время сервера:</strong> ' . date('Y-m-d H:i:s') . '</p>';
        echo '<p><strong>Текущая директория:</strong> ' . __DIR__ . '</p>';
        echo '<p><strong>ОС сервера:</strong> ' . php_uname('s') . '</p>';
        echo '</div>';
        
        echo '<div class="info-card">';
        echo '<h2>🛠️ Проверка расширений PHP</h2>';
        $extensions = ['json', 'mysqli', 'gd', 'curl', 'mbstring', 'xml'];
        foreach($extensions as $ext) {
            $status = extension_loaded($ext) ? '✅' : '❌';
            echo "<p>$status $ext</p>";
        }
        echo '</div>';
        
        echo '<div class="info-card">';
        echo '<h2>📁 Файлы в текущей директории</h2>';
        $files = scandir('.');
        echo '<div class="tech-list">';
        foreach($files as $file) {
            if ($file != '.' && $file != '..') {
                echo '<div class="tech-item">' . $file . '</div>';
            }
        }
        echo '</div>';
        echo '</div>';
        
        echo '<div class="info-card">';
        echo '<h2>⚙️ Конфигурация PHP</h2>';
        echo '<p><strong>Memory Limit:</strong> ' . ini_get('memory_limit') . '</p>';
        echo '<p><strong>Max Execution Time:</strong> ' . ini_get('max_execution_time') . 's</p>';
        echo '<p><strong>Upload Max Filesize:</strong> ' . ini_get('upload_max_filesize') . '</p>';
        echo '<p><strong>Post Max Size:</strong> ' . ini_get('post_max_size') . '</p>';
        echo '</div>';
        ?>
    </div>
</body>
</html>