<?php
// Настройки сессии ДО запуска сессии
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключаем классы для работы с БД
require_once 'db.php';
require_once 'MasterClassRegistration.php';

// 🔥 ЛАБОРАТОРНАЯ 6: Подключаем Redis
require_once 'RedisService.php';
require_once 'Lab6Controller.php';

// Получаем данные из формы
$name = htmlspecialchars($_POST['name'] ?? '');
$birthdate = htmlspecialchars($_POST['birthdate'] ?? '');
$topic = htmlspecialchars($_POST['topic'] ?? '');
$format = htmlspecialchars($_POST['format'] ?? '');
$materials = isset($_POST['materials']) ? 'Да' : 'Нет';
$email = htmlspecialchars($_POST['email'] ?? '');

// Валидация данных
$errors = [];

if (empty($name)) {
    $errors[] = "ФИО не может быть пустым";
}

if (empty($birthdate)) {
    $errors[] = "Дата рождения обязательна";
} else {
    $birthDate = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    if ($age < 18) {
        $errors[] = "Для регистрации необходимо быть старше 18 лет";
    }
}

if (empty($topic)) {
    $errors[] = "Выберите направление мастер-класса";
}

if (empty($format)) {
    $errors[] = "Выберите формат участия";
}

if (empty($email)) {
    $errors[] = "Email обязателен для заполнения";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Некорректный формат email";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: index.php");
    exit();
}

try {
    // Сохраняем в базу данных MySQL
    $registration = new MasterClassRegistration();
    $dbSuccess = $registration->addRegistration($name, $birthdate, $topic, $format, $materials, $email);

    if (!$dbSuccess) {
        throw new Exception("Ошибка сохранения в базу данных");
    }

    // 🔥 ЛАБОРАТОРНАЯ 6: Сохраняем в Redis
    $lab6Controller = new Lab6Controller();
    
    $formData = [
        'name' => $name,
        'birthdate' => $birthdate,
        'topic' => $topic,
        'format' => $format,
        'materials' => $materials,
        'email' => $email
    ];
    
    // Обрабатываем регистрацию в Redis
    $registrationId = $lab6Controller->processRegistration($formData);
    
    // Логируем успешную интеграцию с Redis
    error_log("🎉 LAB6: Registration processed in Redis with ID: " . $registrationId);

    // Также сохраняем в файл для обратной совместимости
    $dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $topic . ";" . $format . ";" . $materials . ";" . $email . "\n";
    file_put_contents("data.txt", $dataLine, FILE_APPEND);

    // Сохраняем данные в сессию
    $_SESSION['form_data'] = [
        'name' => $name,
        'birthdate' => $birthdate,
        'topic' => $topic,
        'format' => $format,
        'materials' => $materials,
        'email' => $email,
        'redis_id' => $registrationId // Добавляем ID из Redis
    ];

    // 🔥 ПОДКЛЮЧЕНИЕ К API ART INSTITUTE OF CHICAGO
    $apiData = getArtworksFromAPI();

    // Сохраняем данные API в сессии для отображения на странице списка
    $_SESSION['api_data'] = $apiData;

    // Устанавливаем куку о последней отправке формы
    setcookie("last_submission", date('Y-m-d H:i:s'), time() + 3600, "/");

    // Перенаправляем на страницу со списком художественных техник
    header("Location: techniques.php");
    exit();

} catch (Exception $e) {
    // Обработка ошибок БД и Redis
    error_log("Database/Redis error: " . $e->getMessage());
    
    // Пытаемся сохранить хотя бы в файл, если другие системы не работают
    try {
        $dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $topic . ";" . $format . ";" . $materials . ";" . $email . "\n";
        file_put_contents("data.txt", $dataLine, FILE_APPEND);
        
        // Сохраняем в сессию даже при ошибках Redis
        $_SESSION['form_data'] = [
            'name' => $name,
            'birthdate' => $birthdate,
            'topic' => $topic,
            'format' => $format,
            'materials' => $materials,
            'email' => $email,
            'warning' => 'Данные сохранены в файл и БД. Redis временно недоступен.'
        ];
        
        // Все равно перенаправляем на success страницу
        header("Location: techniques.php");
        exit();
        
    } catch (Exception $fileException) {
        // Если даже файл не работает, показываем ошибку
        $errors[] = "Произошла критическая ошибка при сохранении данных. Пожалуйста, попробуйте еще раз.";
        $_SESSION['errors'] = $errors;
        header("Location: index.php");
        exit();
    }
}

/**
 * Функция для получения данных из API Art Institute of Chicago
 * Работает без Composer и Guzzle
 */
function getArtworksFromAPI() {
    $url = 'https://api.artic.edu/api/v1/artworks?limit=8&fields=id,title,artist_display,medium_display,date_display,artist_title';
    
    try {
        // Используем file_get_contents с контекстом для HTTPS
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Не удалось подключиться к API');
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Ошибка декодирования JSON: ' . json_last_error_msg());
        }
        
        return $data;
        
    } catch (Exception $e) {
        // Если API не доступно, возвращаем демо-данные
        error_log("API Error: " . $e->getMessage());
        return getDemoArtData();
    }
}

/**
 * Демо-данные на случай недоступности API
 */
function getDemoArtData() {
    return [
        'data' => [
            [
                'id' => 1,
                'title' => 'The Bedroom',
                'artist_display' => 'Vincent van Gogh\nDutch, 1853-1890',
                'artist_title' => 'Vincent van Gogh',
                'medium_display' => 'Oil on canvas',
                'date_display' => '1889'
            ],
            [
                'id' => 2,
                'title' => 'Water Lilies',
                'artist_display' => 'Claude Monet\nFrench, 1840-1926',
                'artist_title' => 'Claude Monet', 
                'medium_display' => 'Oil on canvas',
                'date_display' => '1916'
            ],
            [
                'id' => 3,
                'title' => 'American Gothic',
                'artist_display' => 'Grant Wood\nAmerican, 1891-1942',
                'artist_title' => 'Grant Wood',
                'medium_display' => 'Oil on beaverboard',
                'date_display' => '1930'
            ],
            [
                'id' => 4,
                'title' => 'Starry Night and the Astronauts',
                'artist_display' => 'Alma Thomas\nAmerican, 1891-1978',
                'artist_title' => 'Alma Thomas',
                'medium_display' => 'Acrylic on canvas',
                'date_display' => '1972'
            ],
            [
                'id' => 5,
                'title' => 'A Sunday on La Grande Jatte',
                'artist_display' => 'Georges Seurat\nFrench, 1859-1891',
                'artist_title' => 'Georges Seurat',
                'medium_display' => 'Oil on canvas',
                'date_display' => '1884'
            ]
        ],
        'pagination' => [
            'total' => 5,
            'limit' => 8,
            'offset' => 0,
            'total_pages' => 1,
            'current_page' => 1
        ],
        'info' => [
            'license_text' => 'Demo data - API temporarily unavailable',
            'license_links' => [],
            'version' => '1.0'
        ],
        'config' => [
            'iiif_url' => 'https://www.artic.edu/iiif/2',
            'website_url' => 'https://www.artic.edu'
        ]
    ];
}
?>