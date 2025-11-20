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
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.9em;
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
        .data-source {
            text-align: center;
            margin: 15px 0;
            color: #7f8c8d;
            font-style: italic;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8em;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Все сохраненные данные</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/about.html" class="nav-button">👨‍💻 О нас</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/view.php" class="nav-button">📊 Просмотр данных</a>
            <a href="/info.php" class="nav-button">⚙️ PHP Info</a>
	    <a href="/redis-dashboard.php" class="nav-button">🔴 Redis Dashboard</a>
        </div>

        <?php
        require_once 'MasterClassRegistration.php';
        
        try {
            $registration = new MasterClassRegistration();
            $dbData = $registration->getAllRegistrations();
            $dbCount = $registration->getRegistrationCount();
            
            // Также читаем данные из файла для сравнения
            $filename = "data.txt";
            $fileCount = 0;
            $fileData = [];
            
            if(file_exists($filename) && filesize($filename) > 0){
                $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $fileCount = count($lines);
                foreach($lines as $line){
                    $fileData[] = explode(";", $line);
                }
            }
            
            $totalRecords = $dbCount + $fileCount;
            
            echo '<div class="stats">';
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $totalRecords . '</div>';
            echo '<div>Всего записей</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $dbCount . '</div>';
            echo '<div>В базе данных</div>';
            echo '</div>';
            
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $fileCount . '</div>';
            echo '<div>В файле</div>';
            echo '</div>';
            echo '</div>';
            
            if($dbCount > 0) {
                echo '<div class="data-source">📁 Данные из MySQL базы данных</div>';
                
                echo '<table class="data-table">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>ID</th>';
                echo '<th>ФИО</th>';
                echo '<th>Дата рождения</th>';
                echo '<th>Тема</th>';
                echo '<th>Формат</th>';
                echo '<th>Материалы</th>';
                echo '<th>Email</th>';
                echo '<th>Дата регистрации</th>';
                echo '<th>Действия</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                foreach($dbData as $row){
                    $topicNames = [
                        'webdev' => 'Веб-разработка',
                        'design' => 'UI/UX дизайн',
                        'marketing' => 'Цифровой маркетинг', 
                        'data' => 'Анализ данных',
                        'mobile' => 'Мобильная разработка'
                    ];
                    
                    $formatDisplay = $row['format'] == 'online' ? '🎥 Онлайн' : '🏢 Очно';
                    $materialsDisplay = $row['materials'] == 'Да' ? '✅ Да' : '❌ Нет';
                    $topicDisplay = $topicNames[$row['topic']] ?? $row['topic'];
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['birthdate']) . "</td>";
                    echo "<td>" . htmlspecialchars($topicDisplay) . "</td>";
                    echo "<td>" . $formatDisplay . "</td>";
                    echo "<td>" . $materialsDisplay . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                    echo "<td>";
                    echo "<button class='delete-btn' onclick='deleteRecord(" . $row['id'] . ")'>🗑️ Удалить</button>";
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo '</tbody>';
                echo '</table>';
            }
            
            // Показываем данные из файла, если они есть
            if($fileCount > 0) {
                echo '<div class="data-source" style="margin-top: 40px;">📄 Данные из текстового файла (исторические)</div>';
                
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
                
                foreach($fileData as $lineData){
                    list($datetime, $name, $birthdate, $topic, $format, $materials, $email) = $lineData;
                    
                    $topicNames = [
                        'webdev' => 'Веб-разработка',
                        'design' => 'UI/UX дизайн',
                        'marketing' => 'Цифровой маркетинг', 
                        'data' => 'Анализ данных',
                        'mobile' => 'Мобильная разработка'
                    ];
                    
                    $formatDisplay = $format == 'online' ? '🎥 Онлайн' : '🏢 Очно';
                    $materialsDisplay = $materials == 'Да' ? '✅ Да' : '❌ Нет';
                    $topicDisplay = $topicNames[$topic] ?? $topic;
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($datetime) . "</td>";
                    echo "<td>" . htmlspecialchars($name) . "</td>";
                    echo "<td>" . htmlspecialchars($birthdate) . "</td>";
                    echo "<td>" . htmlspecialchars($topicDisplay) . "</td>";
                    echo "<td>" . $formatDisplay . "</td>";
                    echo "<td>" . $materialsDisplay . "</td>";
                    echo "<td>" . htmlspecialchars($email) . "</td>";
                    echo "</tr>";
                }
                
                echo '</tbody>';
                echo '</table>';
            }
            
            if($totalRecords === 0) {
                echo '<div class="empty-data">';
                echo '<h3>📝 Данных пока нет</h3>';
                echo '<p>Заполните форму регистрации, чтобы увидеть здесь данные.</p>';
                echo '<a href="/master-class.html" class="nav-button">📚 Перейти к форме</a>';
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="empty-data">';
            echo '<h3>❌ Ошибка подключения к базе данных</h3>';
            echo '<p>Проверьте настройки подключения к MySQL.</p>';
            echo '<p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>';
            echo '</div>';
        }
        ?>
    </div>

    <script>
    function deleteRecord(id) {
        if (confirm('Вы уверены, что хотите удалить эту запись?')) {
            fetch('delete_registration.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Запись успешно удалена');
                    location.reload();
                } else {
                    alert('Ошибка при удалении: ' + data.message);
                }
            })
            .catch(error => {
                alert('Ошибка сети: ' + error);
            });
        }
    }
    </script>
</body>
</html>