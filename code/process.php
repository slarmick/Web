<?php
session_start();

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

// Сохраняем данные в сессию
$_SESSION['form_data'] = [
    'name' => $name,
    'birthdate' => $birthdate,
    'topic' => $topic,
    'format' => $format,
    'materials' => $materials,
    'email' => $email
];

// Сохраняем данные в файл
$dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $topic . ";" . $format . ";" . $materials . ";" . $email . "\n";
file_put_contents("data.txt", $dataLine, FILE_APPEND);

// Шаг 2 из задания: Интеграция API после успешной обработки формы
require_once 'ApiClient.php';
$api = new ApiClient();

// Используем API Art Institute of Chicago для получения списка художественных техник
$url = 'https://api.artic.edu/api/v1/artworks?limit=10&fields=title,artist_display,medium_display';
$apiData = $api->request($url);

// Отладочная информация
error_log("API Data received: " . print_r($apiData, true));

// Сохраняем данные API в сессию для отображения на главной странице
$_SESSION['api_data'] = $apiData;

// Проверяем что данные сохранились в сессию
error_log("Session data after API: " . print_r($_SESSION, true));

// Устанавливаем куку о последней отправке формы
setcookie("last_submission", date('Y-m-d H:i:s'), time() + 3600, "/");

// Перенаправляем на главную страницу
header("Location: index.php");
exit();
?>