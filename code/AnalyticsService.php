<?php
require_once 'db.php';

class AnalyticsService {
    private $pdo;
    private $clickhouse;

    public function __construct() {
        $this->pdo = getDB();
        $this->initClickhouse();
    }

    private function initClickhouse() {
        try {
            $this->clickhouse = new PDO(
                'clickhouse:host=clickhouse;port=8123;dbname=lab6_db',
                'lab6_user',
                'lab6_pass'
            );
            
            // Создаем таблицу для аналитики, если не существует
            $this->createAnalyticsTable();
        } catch (Exception $e) {
            error_log("ClickHouse connection error: " . $e->getMessage());
        }
    }

    private function createAnalyticsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS registration_analytics (
            event_date Date DEFAULT today(),
            event_time DateTime DEFAULT now(),
            topic String,
            format String,
            materials String,
            age_group String,
            hour_of_day Int8
        ) ENGINE = MergeTree()
        PARTITION BY toYYYYMM(event_date)
        ORDER BY (event_date, topic, format)
        ";
        
        $this->clickhouse->exec($sql);
    }

    public function recordRegistration($name, $birthdate, $topic, $format, $materials, $email) {
        if (!$this->clickhouse) return false;

        try {
            // Вычисляем возрастную группу
            $birthDate = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            $ageGroup = $this->getAgeGroup($age);
            
            $hour = (int)date('G');

            $sql = "INSERT INTO registration_analytics 
                    (topic, format, materials, age_group, hour_of_day) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->clickhouse->prepare($sql);
            return $stmt->execute([$topic, $format, $materials, $ageGroup, $hour]);
            
        } catch (Exception $e) {
            error_log("Analytics recording error: " . $e->getMessage());
            return false;
        }
    }

    private function getAgeGroup($age) {
        if ($age < 25) return '18-24';
        if ($age < 35) return '25-34';
        if ($age < 45) return '35-44';
        return '45+';
    }

    public function getRegistrationStats() {
        if (!$this->clickhouse) return [];

        try {
            $queries = [
                'total_registrations' => "SELECT count() as count FROM registration_analytics",
                'by_topic' => "SELECT topic, count() as count FROM registration_analytics GROUP BY topic ORDER BY count DESC",
                'by_format' => "SELECT format, count() as count FROM registration_analytics GROUP BY format",
                'by_age_group' => "SELECT age_group, count() as count FROM registration_analytics GROUP BY age_group",
                'by_hour' => "SELECT hour_of_day, count() as count FROM registration_analytics GROUP BY hour_of_day ORDER BY hour_of_day",
                'today_stats' => "SELECT count() as count FROM registration_analytics WHERE event_date = today()"
            ];

            $results = [];
            foreach ($queries as $key => $sql) {
                $stmt = $this->clickhouse->query($sql);
                $results[$key] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $results;

        } catch (Exception $e) {
            error_log("Analytics query error: " . $e->getMessage());
            return [];
        }
    }
}
?>