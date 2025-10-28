<?php
require_once 'db.php';

class MasterClassRegistration {
    private $pdo;

    public function __construct() {
        $this->pdo = getDB();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $sql = "
        CREATE TABLE IF NOT EXISTS master_class_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            birthdate DATE NOT NULL,
            topic VARCHAR(100) NOT NULL,
            format ENUM('online', 'offline') NOT NULL,
            materials ENUM('Да', 'Нет') NOT NULL,
            email VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Ошибка создания таблицы: " . $e->getMessage());
        }
    }

    public function addRegistration($name, $birthdate, $topic, $format, $materials, $email) {
        $sql = "INSERT INTO master_class_registrations (name, birthdate, topic, format, materials, email) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $birthdate, $topic, $format, $materials, $email]);
    }

    public function getAllRegistrations() {
        $sql = "SELECT * FROM master_class_registrations ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getRegistrationCount() {
        $sql = "SELECT COUNT(*) as count FROM master_class_registrations";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch()['count'];
    }

    public function deleteRegistration($id) {
        $sql = "DELETE FROM master_class_registrations WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getRegistrationById($id) {
        $sql = "SELECT * FROM master_class_registrations WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>