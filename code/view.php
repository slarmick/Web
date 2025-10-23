<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр данных - Лабораторные работы</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            max-width: 1000px; 
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
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .data-table th {
            background: #3498db;
            color: white;
        }
        .data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .data-table tr:hover {
            background: #e8f4fd;
        }
        .empty-data {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            color: #7f8c8d;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #3498db;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Все сохраненные данные</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/about.html" class="nav-button">👨‍💻 О нас</a>
            <a href="/test.php" class="nav-button">🧪 PHP Test</a>
        </div>

        <?php
        $filename = "data.txt";
        
        if(file_exists($filename) && filesize($filename) > 0){
            $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $totalRecords = count($lines);
            
            echo '<div class="stats">';
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $totalRecords . '</div>';
            echo '<div>Всего записей</div>';
            echo '</div>';
            echo '</div>';
            
            echo '<table class="data-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Дата и время</th>';
            echo '<th>ФИО</th>';
            echo '<th>Дата рождения</th>';
            echo '<th>Тема</th>';
            echo '<th>Формат</th>';
            echo '<th>Материалы</th>';
            echo '<th>Email</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach($lines as $line){
                list($datetime, $name, $birthdate, $topic, $format, $materials, $email) = explode(";", $line);
                
                $topicNames = [
                    'webdev' => 'Веб-разработка',
                    'design' => 'UI/UX дизайн',
                    'marketing' => 'Цифровой маркетинг', 
                    'data' => 'Анализ данных',
                    'mobile' => 'Мобильная разработка'
                ];
                
                $formatDisplay = $format == 'online' ? '🎥 Онлайн' : '🏢 Очно';
                $materialsDisplay = $materials == 'Да' ? '✅ Да' : '❌ Нет';
                $techniqueDisplay = htmlspecialchars($topic);
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($datetime) . "</td>";
                echo "<td>" . htmlspecialchars($name) . "</td>";
                echo "<td>" . htmlspecialchars($birthdate) . "</td>";
                echo "<td>" . htmlspecialchars($techniqueDisplay) . "</td>";
                echo "<td>" . $formatDisplay . "</td>";
                echo "<td>" . $materialsDisplay . "</td>";
                echo "<td>" . htmlspecialchars($email) . "</td>";
                echo "</tr>";
            }
            
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="empty-data">';
            echo '<h3>📝 Данных пока нет</h3>';
            echo '<p>Заполните форму регистрации, чтобы увидеть здесь данные.</p>';
            echo '<a href="/master-class.html" class="nav-button">📚 Перейти к форме</a>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>