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

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
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

    public function getRegistrationStats() {
        $stats = [];
        
        try {
            // Общее количество
            $stats['total'] = $this->getRegistrationCount();
            
            // По темам
            $sql = "SELECT topic, COUNT(*) as count FROM master_class_registrations GROUP BY topic ORDER BY count DESC";
            $stmt = $this->pdo->query($sql);
            $stats['by_topic'] = $stmt->fetchAll();
            
            // По форматам
            $sql = "SELECT format, COUNT(*) as count FROM master_class_registrations GROUP BY format";
            $stmt = $this->pdo->query($sql);
            $stats['by_format'] = $stmt->fetchAll();
            
            // По материалам
            $sql = "SELECT materials, COUNT(*) as count FROM master_class_registrations GROUP BY materials";
            $stmt = $this->pdo->query($sql);
            $stats['by_materials'] = $stmt->fetchAll();
            
            // За сегодня
            $sql = "SELECT COUNT(*) as count FROM master_class_registrations WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            $stats['today'] = $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Ошибка получения статистики: " . $e->getMessage());
        }
        
        return $stats;
    }

    public function getUniqueEmails() {
        $sql = "SELECT COUNT(DISTINCT email) as count FROM master_class_registrations";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getAverageRegistrationsPerDay() {
        $sql = "SELECT 
                COUNT(*) / COUNT(DISTINCT DATE(created_at)) as avg_per_day
                FROM master_class_registrations
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return round($result['avg_per_day'] ?? 0, 2);
    }
}
?>