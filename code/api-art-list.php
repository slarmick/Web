<?php
session_start();
require_once 'ApiClient.php';

$api = new ApiClient();
$artData = $api->getArtworksList();

header('Content-Type: application/json');
echo json_encode($artData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>