<?php
session_start();

// Получаем данные из формы
$name = htmlspecialchars($_POST['name'] ?? '');
$birthdate = htmlspecialchars($_POST['birthdate'] ?? '');
$topic = htmlspecialchars($_POST['topic'] ?? '');
$format = htmlspecialchars($_POST['format'] ?? '');
$materials = isset($_POST['materials']) ? 'Да' : 'Нет';
$email = htmlspecialchars($_POST['email'] ?? '');

// Подтверждение данных
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

if (empty($topic)) {
    $errors[] = "Выберите тему мастер-класса";
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
    'topic' => $topic,
    'format' => $format,
    'materials' => $materials,
    'email' => $email
];

// Сохраняем данные в файл
$dataLine = date('Y-m-d H:i:s') . ";" . $name . ";" . $birthdate . ";" . $topic . ";" . $format . ";" . $materials . ";" . $email . "\n";
file_put_contents("data.txt", $dataLine, FILE_APPEND);

// Перенаправляем на главную страницу
header("Location: index.php");
exit();
?>