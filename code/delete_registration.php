<?php
require_once 'MasterClassRegistration.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        try {
            $registration = new MasterClassRegistration();
            $success = $registration->deleteRegistration($id);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Запись удалена']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Не удалось удалить запись']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID не указан']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>