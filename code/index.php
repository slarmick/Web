<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная - Лабораторные работы</title>
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
            text-align: center;
        }
        .nav-button:hover {
            background: white;
            color: #3498db;
            transform: translateY(-2px);
        }
        .lab-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease;
        }
        .lab-card:hover {
            transform: translateY(-5px);
        }
        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        .tech-tag {
            background: #e74c3c;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        .feature-list li:before {
            content: "✅";
            position: absolute;
            left: 0;
        }
        .status-badge {
            background: #27ae60;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        .quick-link {
            background: #3498db;
            color: white;
            padding: 20px;
            text-decoration: none;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .quick-link:hover {
            background: #2980b9;
            transform: translateY(-3px);
        }
        .session-data {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .errors {
            background: #fde8e8;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #e74c3c;
            color: #c0392b;
        }
        .errors ul {
            margin: 0;
            padding-left: 20px;
        }
        .php-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        .data-count {
            background: #d4edda;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .user-info {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .api-data {
            background: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .artworks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .artwork-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        .artwork-card img {
            border-radius: 5px;
            margin-top: 10px;
            max-width: 100%;
            height: auto;
        }
        .api-error {
            background: #fde8e8;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #e74c3c;
            color: #c0392b;
        }
        .api-raw-data {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
        }
        /* Стили для списка художественных техник */
        .techniques-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        .technique-item {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .technique-item:hover {
            transform: translateX(5px);
            border-left-color: #e74c3c;
        }
        .artwork-title {
            color: #2c3e50;
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        .artwork-artist {
            color: #3498db;
            margin-bottom: 5px;
            font-style: italic;
        }
        .artwork-technique {
            color: #e74c3c;
            font-weight: bold;
        }
        .api-source {
            text-align: center;
            margin-top: 15px;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .techniques-count {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .debug-panel {
            background: #ffeb3b;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #ff9800;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Лабораторные работы по Docker & Nginx</h1>

        <div class="nav-buttons">
            <a href="/" class="nav-button">🏠 Главная</a>
            <a href="/about.html" class="nav-button">👨‍💻 О нас</a>
            <a href="/master-class.html" class="nav-button">📚 Форма регистрации</a>
            <a href="/view.php" class="nav-button">📊 Просмотр данных</a>
            <a href="/redis-dashboard.php" class="nav-button">🔴 Redis Dashboard</a>
            <a href="/info.php" class="nav-button">⚙️ PHP Info</a>
        </div>

        <!-- Вывод ошибок валидации -->
        <?php if(isset($_SESSION['errors'])): ?>
            <div class="errors">
                <h3>❌ Ошибки при заполнении формы:</h3>
                <ul>
                    <?php foreach($_SESSION['errors'] as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <!-- Вывод данных из сессии -->
        <?php if(isset($_SESSION['form_data'])): ?>
            <div class="session-data">
                <h3>✅ Данные успешно сохранены!</h3>
                <p><strong>ФИО:</strong> <?= $_SESSION['form_data']['name'] ?></p>
                <p><strong>Дата рождения:</strong> <?= $_SESSION['form_data']['birthdate'] ?></p>
                <p><strong>Тема:</strong> 
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
                <p><strong>Формат:</strong> <?= $_SESSION['form_data']['format'] == 'online' ? '🎥 Онлайн' : '🏢 Очно' ?></p>
                <p><strong>Материалы:</strong> <?= $_SESSION['form_data']['materials'] == 'Да' ? '✅ Да (+500₽)' : '❌ Нет' ?></p>
                <p><strong>Email:</strong> <?= $_SESSION['form_data']['email'] ?></p>
                <p><em>Данные сохранены в сессии и записаны в файл data.txt</em></p>
            </div>
            <?php unset($_SESSION['form_data']); ?>
        <?php endif; ?>

        <!-- Информация о пользователе -->
        <?php
        require_once 'UserInfo.php';
        $userInfo = UserInfo::getInfo();
        ?>
        <div class="user-info">
            <h3>👤 Информация о вашем посещении:</h3>
            <p><strong>IP-адрес:</strong> <?= htmlspecialchars($userInfo['ip']) ?></p>
            <p><strong>Браузер:</strong> <?= UserInfo::getBrowserInfo() ?></p>
            <p><strong>Время на сервере:</strong> <?= $userInfo['server_time'] ?></p>
            <p><strong>Последняя отправка формы:</strong> <?= $userInfo['last_submission'] ?></p>
        </div>

<!-- Статистика базы данных -->
<?php
try {
    require_once 'MasterClassRegistration.php';
    $registration = new MasterClassRegistration();
    $dbCount = $registration->getRegistrationCount();
    
    $filename = "data.txt";
    $fileCount = 0;
    if(file_exists($filename) && filesize($filename) > 0){
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $fileCount = count($lines);
    }
    
    $totalRecords = $dbCount + $fileCount;
?>
    <div class="data-count">
        <h3>📊 Статистика данных</h3>
        <p>Всего сохраненных записей: <strong><?= $totalRecords ?></strong></p>
        <p>В базе данных: <strong><?= $dbCount ?></strong> | В файле: <strong><?= $fileCount ?></strong></p>
        <a href="/view.php" class="nav-button" style="display: inline-block; padding: 8px 20px; margin-top: 10px;">
            📋 Посмотреть все данные
        </a>
    </div>
<?php
} catch (Exception $e) {
    // Игнорируем ошибки БД на главной странице
}
?>

        <!-- Вывод списка художественных техник из API -->
        <?php if(isset($_SESSION['api_data'])): ?>
            <div class="api-data">
                <h3>🎨 Список художественных техник из коллекции музея</h3>
                
                <?php 
                // Определяем где находятся данные в ответе API
                $artworks = $_SESSION['api_data']['data'] ?? [];
                $total = $_SESSION['api_data']['pagination']['total'] ?? count($artworks);
                ?>
                
                <?php if(!empty($artworks) && is_array($artworks)): ?>
                    <div class="techniques-count">
                        Найдено <?= count($artworks) ?> произведений из <?= $total ?> в коллекции
                    </div>
                    <div class="techniques-list">
                        <?php foreach($artworks as $artwork): ?>
                            <div class="technique-item">
                                <div class="artwork-title"><strong><?= htmlspecialchars($artwork['title'] ?? 'Без названия') ?></strong></div>
                                <div class="artwork-artist">👨‍🎨 <?= htmlspecialchars($artwork['artist_display'] ?? 'Неизвестен') ?></div>
                                <div class="artwork-technique">🎨 <?= htmlspecialchars($artwork['medium_display'] ?? 'Техника не указана') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="api-source">
                        <em>Источник: Art Institute of Chicago API</em>
                    </div>
                    
                <?php else: ?>
                    <div class="api-error">
                        <p>❌ Не удалось загрузить список художественных техник</p>
                        <p><small>Данные API: <?= htmlspecialchars(print_r($_SESSION['api_data'], true)) ?></small></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php unset($_SESSION['api_data']); ?>
        <?php endif; ?>

        <!-- Информация о количестве записей -->
        <?php
        $filename = "data.txt";
        $totalRecords = 0;
        if(file_exists($filename) && filesize($filename) > 0){
            $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $totalRecords = count($lines);
        }
        ?>
        
        <?php if($totalRecords > 0): ?>
            <div class="data-count">
                <h3>📊 Статистика данных</h3>
                <p>Всего сохраненных записей: <strong><?= $totalRecords ?></strong></p>
                <a href="/view.php" class="nav-button" style="display: inline-block; padding: 8px 20px; margin-top: 10px;">
                    📋 Посмотреть все данные
                </a>
            </div>
        <?php endif; ?>

        <h2>📋 Быстрый доступ</h2>
        <div class="quick-links">
            <a href="/about.html" class="quick-link">
                <h3>👨‍💻 О проекте</h3>
                <p>Информация о лабораторных работах</p>
            </a>
            <a href="/master-class.html" class="quick-link">
                <h3>📝 Форма регистрации</h3>
                <p>Заполнить форму (PHP обработка)</p>
            </a>
            <a href="/view.php" class="quick-link">
                <h3>📊 Все данные</h3>
                <p>Просмотр всех записей</p>
            </a>
            <a href="/test.php" class="quick-link">
                <h3>🧪 Тест PHP</h3>
                <p>Проверка работы PHP</p>
            </a>
        </div>

        <div class="lab-card">
            <h3>🚀 Лабораторная работа №1 <span class="status-badge">Завершена</span></h3>
            <p><strong>Тема:</strong> Веб-сервер в Docker (Nginx + HTML)</p>
            <div class="tech-stack">
                <span class="tech-tag">Docker</span>
                <span class="tech-tag">Nginx</span>
                <span class="tech-tag">HTML5</span>
                <span class="tech-tag">CSS3</span>
            </div>
            <ul class="feature-list">
                <li>Настройка Nginx в Docker контейнере</li>
                <li>Создание кастомных HTML страниц</li>
                <li>Настройка volumes для live-обновлений</li>
                <li>Работа с портами и навигацией</li>
            </ul>
        </div>

        <div class="lab-card">
            <h3>🔧 Лабораторная работа №2 <span class="status-badge">Завершена</span></h3>
            <p><strong>Тема:</strong> Настройка Nginx + PHP-FPM. Основы HTML-форм и обработка на JavaScript.</p>
            <div class="tech-stack">
                <span class="tech-tag">PHP 8.2</span>
                <span class="tech-tag">PHP-FPM</span>
                <span class="tech-tag">JavaScript</span>
                <span class="tech-tag">HTML Forms</span>
                <span class="tech-tag">Docker Compose</span>
            </div>
            <ul class="feature-list">
                <li>Настройка связки Nginx + PHP-FPM</li>
                <li>Создание интерактивных HTML форм</li>
                <li>JavaScript обработка без перезагрузки страницы</li>
                <li>Валидация форм на клиентской стороне</li>
                <li>Работа с различными типами полей ввода</li>
            </ul>
        </div>

        <div class="lab-card">
            <h3>💻 Лабораторная работа №3 <span class="status-badge">Завершена</span></h3>
            <p><strong>Тема:</strong> Обработка данных формы на PHP с сохранением в сессии и файл</p>
            <div class="tech-stack">
                <span class="tech-tag">PHP 8.2</span>
                <span class="tech-tag">Сессии PHP</span>
                <span class="tech-tag">Валидация</span>
                <span class="tech-tag">Файлы</span>
                <span class="tech-tag">HTML Forms</span>
            </div>
            <ul class="feature-list">
                <li>Обработка данных формы на стороне сервера через PHP</li>
                <li>Сохранение данных в сессии PHP</li>
                <li>Сохранение данных в текстовый файл</li>
                <li>Валидация данных на стороне сервера</li>
                <li>Вывод всех сохраненных данных на отдельной странице</li>
                <li>Обработка ошибок с пользовательскими сообщениями</li>
            </ul>
        </div>

        <div class="lab-card">
            <h3>🎨 Лабораторная работа №4 <span class="status-badge">Завершена</span></h3>
            <p><strong>Тема:</strong> Composer, классы и работа с публичным API</p>
            <div class="tech-stack">
                <span class="tech-tag">Composer</span>
                <span class="tech-tag">Guzzle HTTP</span>
                <span class="tech-tag">API Integration</span>
                <span class="tech-tag">PHP Classes</span>
                <span class="tech-tag">Cookies</span>
                <span class="tech-tag">Art Institute API</span>
            </div>
            <ul class="feature-list">
                <li>Работа с Composer и внешними библиотеками (Guzzle)</li>
                <li>Создание классов для работы с API</li>
                <li>Интеграция Art Institute of Chicago API</li>
                <li>Отображение художественных техник и произведений</li>
                <li>Работа с куками для хранения информации о пользователе</li>
                <li>Сбор информации о браузере и IP-адресе</li>
            </ul>
        </div>

        <h2>🛠️ Технологии проекта</h2>
        <div class="tech-stack">
            <span class="tech-tag">Docker</span>
            <span class="tech-tag">Docker Compose</span>
            <span class="tech-tag">Nginx 1.27</span>
            <span class="tech-tag">PHP 8.2</span>
            <span class="tech-tag">PHP-FPM</span>
            <span class="tech-tag">HTML5</span>
            <span class="tech-tag">CSS3</span>
            <span class="tech-tag">JavaScript</span>
            <span class="tech-tag">Git</span>
            <span class="tech-tag">Сессии PHP</span>
            <span class="tech-tag">Валидация форм</span>
            <span class="tech-tag">Composer</span>
            <span class="tech-tag">Guzzle HTTP</span>
            <span class="tech-tag">REST API</span>
            <span class="tech-tag">Cookies</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.lab-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });

            const sessionData = document.querySelector('.session-data');
            if (sessionData) {
                sessionData.style.opacity = '0';
                setTimeout(() => {
                    sessionData.style.transition = 'all 0.8s ease';
                    sessionData.style.opacity = '1';
                }, 500);
            }

            const apiData = document.querySelector('.api-data');
            if (apiData) {
                apiData.style.opacity = '0';
                setTimeout(() => {
                    apiData.style.transition = 'all 0.8s ease';
                    apiData.style.opacity = '1';
                }, 700);
            }
        });
    </script>
</body>
</html>