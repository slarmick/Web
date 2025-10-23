<?php
session_start();

// Получаем данные из формы
$name = htmlspecialchars($_POST['name'] ?? '');
$birthdate = htmlspecialchars($_POST['birthdate'] ?? '');
$technique = htmlspecialchars($_POST['technique'] ?? ''); // Изменено с topic на technique
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
    // Проверяем, что возраст не менее 18 лет
    $birthDate = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    if ($age < 18) {
        $errors[] = "Для регистрации необходимо быть старше 18 лет";
    }
}

if (empty($technique)) {
    $errors[] = "Выберите художественную технику";
}

if (empty($format)) {
    $errors[] = "Выберите формат участия";
}

if (empty($email)) {
    $errors[] = "Email обязателен для заполнения";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Некорректный формат email";
}

// Если есть ошибки - сохраняем их в сессию и возвращаем на главную
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: index.php");
    exit();
}

// Сохраняем данные в сессию
$_SESSION['form_data'] = [
    'name' => $name,
    'birthdate' => $birthdate,
    'technique' => $technique, // Изменено с topic на technique
    'format' => $format,
    'materials' => $materials,
    'email' => $email
];

// Сохраняем данные в файл (обновляем разделитель)
$dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $technique . ";" . $format . ";" . $materials . ";" . $email . "\n";
file_put_contents("data.txt", $dataLine, FILE_APPEND);

try {
    require_once 'ApiClient.php';
    $api = new ApiClient();
    
    // Получаем примеры работ для выбранной художественной техники
    $artData = $api->getArtworksByTechnique($technique);

    // Сохраняем данные API в сессию
    $_SESSION['api_data'] = $artData;
} catch (Exception $e) {
    // Если API не работает, сохраняем ошибку
    $_SESSION['api_data'] = [
        'success' => false,
        'error' => 'Не удалось загрузить примеры работ: ' . $e->getMessage()
    ];
}

// Устанавливаем куку о последней отправке
setcookie("last_submission", date('Y-m-d H:i:s'), time() + 3600, "/");

// Перенаправляем на главную страницу
header("Location: index.php");
exit();
?>