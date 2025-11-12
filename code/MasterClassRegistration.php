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
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_topic (topic),
            INDEX idx_format (format),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Ошибка создания таблицы: " . $e->getMessage());
            throw new Exception("Не удалось создать таблицу в базе данных");
        }
    }

    public function addRegistration($name, $birthdate, $topic, $format, $materials, $email) {
        $sql = "INSERT INTO master_class_registrations (name, birthdate, topic, format, materials, email) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$name, $birthdate, $topic, $format, $materials, $email]);
            
            if (!$result) {
                error_log("Ошибка выполнения запроса INSERT");
                return false;
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Ошибка добавления записи: " . $e->getMessage());
            throw new Exception("Не удалось сохранить данные в базу данных");
        }
    }

    public function getLastInsertId() {
        try {
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Ошибка получения последнего ID: " . $e->getMessage());
            return null;
        }
    }

    public function getAllRegistrations($limit = null, $offset = 0) {
        $sql = "SELECT * FROM master_class_registrations ORDER BY created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения всех записей: " . $e->getMessage());
            return [];
        }
    }

    public function getRegistrationCount() {
        $sql = "SELECT COUNT(*) as count FROM master_class_registrations";
        
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Ошибка получения количества записей: " . $e->getMessage());
            return 0;
        }
    }

    public function getRegistrationsByTopic($topic) {
        $sql = "SELECT * FROM master_class_registrations WHERE topic = ? ORDER BY created_at DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$topic]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения записей по теме: " . $e->getMessage());
            return [];
        }
    }

    public function getRegistrationsByFormat($format) {
        $sql = "SELECT * FROM master_class_registrations WHERE format = ? ORDER BY created_at DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$format]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения записей по формату: " . $e->getMessage());
            return [];
        }
    }

    public function getRegistrationById($id) {
        $sql = "SELECT * FROM master_class_registrations WHERE id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения записи по ID: " . $e->getMessage());
            return null;
        }
    }

    public function searchRegistrations($searchTerm) {
        $sql = "SELECT * FROM master_class_registrations 
                WHERE name LIKE ? OR email LIKE ? OR topic LIKE ?
                ORDER BY created_at DESC";
        
        try {
            $searchPattern = "%$searchTerm%";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка поиска записей: " . $e->getMessage());
            return [];
        }
    }

    public function getRegistrationStats() {
        $stats = [];
        
        try {
            // Общее количество
            $stats['total'] = $this->getRegistrationCount();
            
            // По темам
            $sql = "SELECT topic, COUNT(*) as count FROM master_class_registrations GROUP BY topic ORDER BY count DESC";
            $stmt = $this->pdo->query($sql);
            $stats['by_topic'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // По форматам
            $sql = "SELECT format, COUNT(*) as count FROM master_class_registrations GROUP BY format";
            $stmt = $this->pdo->query($sql);
            $stats['by_format'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // По материалам
            $sql = "SELECT materials, COUNT(*) as count FROM master_class_registrations GROUP BY materials";
            $stmt = $this->pdo->query($sql);
            $stats['by_materials'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // За сегодня
            $sql = "SELECT COUNT(*) as count FROM master_class_registrations WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['today'] = $result['count'] ?? 0;
            
            // Последние 7 дней
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                    FROM master_class_registrations 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(created_at) 
                    ORDER BY date DESC";
            $stmt = $this->pdo->query($sql);
            $stats['last_7_days'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения статистики: " . $e->getMessage());
        }
        
        return $stats;
    }

    public function deleteRegistration($id) {
        $sql = "DELETE FROM master_class_registrations WHERE id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
            
        } catch (PDOException $e) {
            error_log("Ошибка удаления записи: " . $e->getMessage());
            return false;
        }
    }

    public function updateRegistration($id, $name, $birthdate, $topic, $format, $materials, $email) {
        $sql = "UPDATE master_class_registrations 
                SET name = ?, birthdate = ?, topic = ?, format = ?, materials = ?, email = ?
                WHERE id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$name, $birthdate, $topic, $format, $materials, $email, $id]);
            
        } catch (PDOException $e) {
            error_log("Ошибка обновления записи: " . $e->getMessage());
            return false;
        }
    }

    public function getRecentRegistrations($limit = 10) {
        $sql = "SELECT * FROM master_class_registrations 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения последних записей: " . $e->getMessage());
            return [];
        }
    }

    public function getRegistrationsByDateRange($startDate, $endDate) {
        $sql = "SELECT * FROM master_class_registrations 
                WHERE created_at BETWEEN ? AND ?
                ORDER BY created_at DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения записей по диапазону дат: " . $e->getMessage());
            return [];
        }
    }

    public function getUniqueEmails() {
        $sql = "SELECT COUNT(DISTINCT email) as count FROM master_class_registrations";
        
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Ошибка получения уникальных email: " . $e->getMessage());
            return 0;
        }
    }

    public function getAverageRegistrationsPerDay() {
        $sql = "SELECT 
                COUNT(*) / COUNT(DISTINCT DATE(created_at)) as avg_per_day
                FROM master_class_registrations
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return round($result['avg_per_day'] ?? 0, 2);
            
        } catch (PDOException $e) {
            error_log("Ошибка получения среднего количества регистраций: " . $e->getMessage());
            return 0;
        }
    }
}
?>