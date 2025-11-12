<?php
require_once 'db.php';

class MasterClassRegistration {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = getDB();
            $this->createTableIfNotExists();
        } catch (Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            error_log("Database connection failed: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    private function createTableIfNotExists() {
        if (!$this->pdo) return false;

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
            return true;
        } catch (PDOException $e) {
            error_log("Ошибка создания таблицы: " . $e->getMessage());
            return false;
        }
    }

    public function addRegistration($name, $birthdate, $topic, $format, $materials, $email) {
        if (!$this->pdo) {
            error_log("No database connection");
            return false;
        }

        $sql = "INSERT INTO master_class_registrations (name, birthdate, topic, format, materials, email) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$name, $birthdate, $topic, $format, $materials, $email]);
        } catch (PDOException $e) {
            error_log("Ошибка добавления записи: " . $e->getMessage());
            return false;
        }
    }

    public function getLastInsertId() {
        if (!$this->pdo) return null;
        
        try {
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Ошибка получения последнего ID: " . $e->getMessage());
            return null;
        }
    }

    public function getAllRegistrations() {
        if (!$this->pdo) return [];
        
        try {
            $sql = "SELECT * FROM master_class_registrations ORDER BY created_at DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Ошибка получения всех записей: " . $e->getMessage());
            return [];
        }
    }

    public function getRegistrationCount() {
        if (!$this->pdo) return 0;
        
        try {
            $sql = "SELECT COUNT(*) as count FROM master_class_registrations";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Ошибка получения количества записей: " . $e->getMessage());
            return 0;
        }
    }

    // Остальные методы остаются аналогичными с проверкой $this->pdo
    public function getRegistrationStats() {
        if (!$this->pdo) return [];
        
        $stats = [];
        try {
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
        if (!$this->pdo) return 0;
        
        try {
            $sql = "SELECT COUNT(DISTINCT email) as count FROM master_class_registrations";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Ошибка получения уникальных email: " . $e->getMessage());
            return 0;
        }
    }

    public function getAverageRegistrationsPerDay() {
        if (!$this->pdo) return 0;
        
        try {
            $sql = "SELECT COUNT(*) / COUNT(DISTINCT DATE(created_at)) as avg_per_day 
                    FROM master_class_registrations 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            return round($result['avg_per_day'] ?? 0, 2);
        } catch (PDOException $e) {
            error_log("Ошибка получения среднего количества: " . $e->getMessage());
            return 0;
        }
    }
}
?>