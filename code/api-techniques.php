<?php
session_start();
require_once 'ApiClient.php';

// Создаем экземпляр API клиента
$api = new ApiClient();

// Получаем художественные техники
$techniquesData = $api->getArtTechniques();

// Устанавливаем заголовки для JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Возвращаем данные в формате JSON
echo json_encode($techniquesData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>