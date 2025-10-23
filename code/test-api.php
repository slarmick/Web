<?php
require_once 'ApiClient.php';

$api = new ApiClient();
$testData = $api->request('https://api.artic.edu/api/v1/artworks?limit=3&fields=title,artist_display,medium_display');

echo "<h2>API Test Results:</h2>";
echo "<pre>";
print_r($testData);
echo "</pre>";

// Проверяем сессию
session_start();
echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>