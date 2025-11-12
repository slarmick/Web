<?php
// НАЧАЛО ФАЙЛА - никакого вывода до этой точки!

// Настройки сессии для Redis ДО любого вывода
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://redis:6379');
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);

// Запускаем сессию СРАЗУ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключаем классы
require_once 'db.php';
require_once 'MasterClassRegistration.php';

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

// Флаг успешного сохранения
$success = false;

try {
    // Сохраняем в MySQL базу данных
    $registration = new MasterClassRegistration();
    $dbSuccess = $registration->addRegistration($name, $birthdate, $topic, $format, $materials, $email);

    if ($dbSuccess) {
        $success = true;
    } else {
        error_log("MySQL save failed, but continuing...");
    }

} catch (Exception $e) {
    error_log("MySQL error: " . $e->getMessage());
    // Продолжаем выполнение даже при ошибке БД
}

// ВСЕГДА сохраняем в файл для обратной совместимости
try {
    $dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $topic . ";" . $format . ";" . $materials . ";" . $email . "\n";
    file_put_contents("data.txt", $dataLine, FILE_APPEND);
    $success = true; // Если файл сохранился, считаем успехом
} catch (Exception $e) {
    error_log("File save error: " . $e->getMessage());
}

// Сохраняем данные в сессию (теперь хранится в Redis!)
$_SESSION['form_data'] = [
    'name' => $name,
    'birthdate' => $birthdate,
    'topic' => $topic,
    'format' => $format,
    'materials' => $materials,
    'email' => $email
];

// Записываем в Redis дополнительную информацию о сессии
if (isset($_SESSION['redis_initialized'])) {
    $_SESSION['registration_count'] = ($_SESSION['registration_count'] ?? 0) + 1;
    $_SESSION['last_registration_time'] = date('Y-m-d H:i:s');
    $_SESSION['preferred_topic'] = $topic;
} else {
    $_SESSION['redis_initialized'] = true;
    $_SESSION['registration_count'] = 1;
    $_SESSION['first_visit'] = date('Y-m-d H:i:s');
    $_SESSION['last_registration_time'] = date('Y-m-d H:i:s');
    $_SESSION['preferred_topic'] = $topic;
}

// ВСЕГДА делаем API вызов, даже если БД не работает
try {
    require_once 'ApiClient.php';
    $api = new ApiClient();
    $url = 'https://api.artic.edu/api/v1/artworks?limit=10&fields=title,artist_display,medium_display';
    $apiData = $api->request($url);
    $_SESSION['api_data'] = $apiData;
} catch (Exception $e) {
    error_log("API call failed: " . $e->getMessage());
    // Создаем демо-данные если API не работает
    $_SESSION['api_data'] = [
        'data' => [
            [
                'title' => 'The Bedroom',
                'artist_display' => 'Vincent van Gogh',
                'medium_display' => 'Oil on canvas'
            ],
            [
                'title' => 'Water Lilies',
                'artist_display' => 'Claude Monet',
                'medium_display' => 'Oil on canvas'
            ],
            [
                'title' => 'American Gothic',
                'artist_display' => 'Grant Wood',
                'medium_display' => 'Oil on beaverboard'
            ]
        ],
        'pagination' => [
            'total' => 3
        ]
    ];
}

// Устанавливаем куку о последней отправке формы
setcookie("last_submission", date('Y-m-d H:i:s'), time() + 3600, "/");

// Перенаправляем на страницу со списком художественных техник
header("Location: techniques.php");
exit();
?>