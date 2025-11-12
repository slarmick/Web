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

try {
    // Сохраняем в MySQL базу данных
    $registration = new MasterClassRegistration();
    $dbSuccess = $registration->addRegistration($name, $birthdate, $topic, $format, $materials, $email);

    if (!$dbSuccess) {
        throw new Exception("Ошибка сохранения в базу данных MySQL");
    }

    // Также сохраняем в файл для обратной совместимости
    $dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $topic . ";" . $format . ";" . $materials . ";" . $email . "\n";
    file_put_contents("data.txt", $dataLine, FILE_APPEND);

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

    // API вызов
    require_once 'ApiClient.php';
    $api = new ApiClient();
    $url = 'https://api.artic.edu/api/v1/artworks?limit=10&fields=title,artist_display,medium_display';
    $apiData = $api->request($url);
    $_SESSION['api_data'] = $apiData;

    // Устанавливаем куку о последней отправке формы
    setcookie("last_submission", date('Y-m-d H:i:s'), time() + 3600, "/");

    // Перенаправляем на страницу со списком художественных техник
    header("Location: techniques.php");
    exit();

} catch (Exception $e) {
    // Обработка ошибок
    error_log("Registration process error: " . $e->getMessage());
    
    // Пытаемся сохранить хотя бы в файл
    try {
        $dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $topic . ";" . $format . ";" . $materials . ";" . $email . "\n";
        file_put_contents("data.txt", $dataLine, FILE_APPEND);
        
        $_SESSION['form_data'] = [
            'name' => $name,
            'birthdate' => $birthdate,
            'topic' => $topic,
            'format' => $format,
            'materials' => $materials,
            'email' => $email
        ];
        
        // Все равно делаем API вызов и редирект
        require_once 'ApiClient.php';
        $api = new ApiClient();
        $url = 'https://api.artic.edu/api/v1/artworks?limit=10&fields=title,artist_display,medium_display';
        $apiData = $api->request($url);
        $_SESSION['api_data'] = $apiData;
        setcookie("last_submission", date('Y-m-d H:i:s'), time() + 3600, "/");
        
        header("Location: techniques.php");
        exit();
        
    } catch (Exception $fallbackError) {
        $errors[] = "Произошла критическая ошибка при сохранении данных. Пожалуйста, попробуйте еще раз.";
        $_SESSION['errors'] = $errors;
        header("Location: index.php");
        exit();
    }
}
?>