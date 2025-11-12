<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список художественных техник - Лабораторные работы</title>
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
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 1.1em;
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
            text-align: center;
        }
        .nav-button:hover {
            background: white;
            color: #3498db;
            transform: translateY(-2px);
        }
        .success-message {
            background: #d4edda;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .techniques-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        .technique-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .technique-item:hover {
            transform: translateX(5px);
            border-left-color: #e74c3c;
        }
        .artwork-title {
            color: #2c3e50;
            font-size: 1.2em;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .artwork-artist {
            color: #3498db;
            margin-bottom: 8px;
            font-style: italic;
            font-size: 1.1em;
        }
        .artwork-technique {
            color: #e74c3c;
            font-weight: bold;
            font-size: 1.1em;
            padding: 8px 12px;
            background: #fde8e8;
            border-radius: 5px;
            display: inline-block;
        }
        .artwork-date {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .techniques-count {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 20px;
            font-size: 1em;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .api-source {
            text-align: center;
            margin-top: 25px;
            color: #7f8c8d;
            font-size: 0.9em;
            font-style: italic;
        }
        .error-message {
            background: #fde8e8;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #e74c3c;
            color: #c0392b;
            text-align: center;
        }
        .registration-info {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .api-status {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .api-live {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .api-demo {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Список художественных техник</h1>
        <div class="subtitle">Примеры различных художественных техник из коллекции Чикагского института искусств</div>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/view.php" class="nav-button">📊 Все записи</a>
        </div>

        <!-- Информация о успешной регистрации -->
        <?php if(isset($_SESSION['form_data'])): ?>
            <div class="success-message">
                <h3>✅ Регистрация успешно завершена!</h3>
                <p><strong>ФИО:</strong> <?= $_SESSION['form_data']['name'] ?></p>
                <p><strong>Направление:</strong> 
                    <?php
                    $topicNames = [
                        'webdev' => 'Веб-разработка для начинающих',
                        'design' => 'UI/UX дизайн',
                        'marketing' => 'Цифровой маркетинг',
                        'data' => 'Анализ данных с Python',
                        'mobile' => 'Мобильная разработка'
                    ];
                    echo $topicNames[$_SESSION['form_data']['topic']] ?? $_SESSION['form_data']['topic'];
                    ?>
                </p>
                <p><em>Ниже представлены примеры художественных техник из коллекции музея</em></p>
            </div>
            <?php unset($_SESSION['form_data']); ?>
        <?php endif; ?>

        <!-- Вывод списка художественных техник из API -->
        <?php if(isset($_SESSION['api_data'])): ?>
            <?php 
            $artworks = $_SESSION['api_data']['data'] ?? [];
            $total = $_SESSION['api_data']['pagination']['total'] ?? count($artworks);
            $isDemoData = isset($_SESSION['api_data']['info']['license_text']) && 
                         strpos($_SESSION['api_data']['info']['license_text'], 'Demo data') !== false;
            ?>
            
            <?php if(!empty($artworks) && is_array($artworks)): ?>
                <div class="api-status <?= $isDemoData ? 'api-demo' : 'api-live' ?>">
                    <?php if($isDemoData): ?>
                        🔧 Используются демо-данные (API временно недоступен)
                    <?php else: ?>
                        ✅ Данные загружены из Art Institute of Chicago API
                    <?php endif; ?>
                </div>
                
                <div class="techniques-count">
                    🖼️ Найдено <strong><?= count($artworks) ?></strong> произведений из <strong><?= $total ?></strong> в коллекции музея
                </div>
                
                <div class="techniques-list">
                    <?php foreach($artworks as $index => $artwork): ?>
                        <div class="technique-item">
                            <div class="artwork-title"><?= ($index + 1) ?>. <?= htmlspecialchars($artwork['title'] ?? 'Без названия') ?></div>
                            <div class="artwork-artist">👨‍🎨 <?= htmlspecialchars($artwork['artist_display'] ?? ($artwork['artist_title'] ?? 'Неизвестен')) ?></div>
                            <div class="artwork-technique">🎨 <?= htmlspecialchars($artwork['medium_display'] ?? 'Техника не указана') ?></div>
                            <?php if(isset($artwork['date_display'])): ?>
                                <div class="artwork-date">📅 <?= htmlspecialchars($artwork['date_display']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="api-source">
                    <em>Данные предоставлены Art Institute of Chicago API</em>
                </div>
                
            <?php else: ?>
                <div class="error-message">
                    <h3>❌ Не удалось загрузить список художественных техник</h3>
                    <p>Попробуйте обновить страницу или вернуться позже</p>
                </div>
            <?php endif; ?>
            
            <?php unset($_SESSION['api_data']); ?>
        <?php else: ?>
            <div class="error-message">
                <h3>❌ Данные не найдены</h3>
                <p>Для просмотра списка художественных техник необходимо сначала зарегистрироваться через форму</p>
                <a href="/master-class.html" class="nav-button" style="display: inline-block; margin-top: 15px;">
                    📝 Перейти к регистрации
                </a>
            </div>
        <?php endif; ?>

        <!-- Дополнительная информация -->
        <div class="registration-info">
            <h3>💡 О проекте</h3>
            <p>Этот список демонстрирует разнообразие художественных техник, используемых в произведениях искусства из коллекции одного из крупнейших музеев мира.</p>
            <p>Каждая техника имеет свои уникальные особенности и требует специальных навыков для освоения.</p>
        </div>

        <div class="nav-buttons">
            <a href="/master-class.html" class="nav-button">📝 Зарегистрироваться снова</a>
            <a href="/view.php" class="nav-button">📊 Посмотреть все записи</a>
            <a href="/" class="nav-button">🏠 На главную</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Анимация появления элементов
            const items = document.querySelectorAll('.technique-item');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>