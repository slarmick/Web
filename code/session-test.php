<?php
session_start();

echo "<h2>Session Test</h2>";

// Проверяем текущую сессию
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

// Пробуем записать в сессию
$_SESSION['test_time'] = date('Y-m-d H:i:s');
$_SESSION['test_data'] = 'Hello World';

echo "<h3>Session Data After Write:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Проверяем куки
echo "<h3>Cookies:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Проверяем права на запись сессий
echo "<h3>Session Save Path:</h3>";
echo "<p>" . session_save_path() . "</p>";
echo "<p>Writable: " . (is_writable(session_save_path()) ? 'YES' : 'NO') . "</p>";
?>