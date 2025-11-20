<?php
class ClickHouseService {
    private $client;
    private $isConnected = false;

    public function __construct() {
        try {
            $this->client = new GuzzleHttp\Client([
                'base_uri' => 'http://clickhouse:8123/',
                'timeout' => 5.0,
                'headers' => [
                    'X-ClickHouse-User' => 'default',
                    'X-ClickHouse-Key' => 'lab6_pass'
                ]
            ]);
            
            $this->initializeDatabase();
            $this->isConnected = true;
        } catch (Exception $e) {
            error_log("ClickHouse connection failed: " . $e->getMessage());
            $this->isConnected = false;
        }
    }

    private function initializeDatabase() {
        if (!$this->isConnected) return;

        // Создаем таблицу для аналитики регистраций
        $queries = [
            "CREATE DATABASE IF NOT EXISTS lab6_stats",
            "CREATE TABLE IF NOT EXISTS lab6_stats.registrations (
                id UUID,
                name String,
                email String,
                topic String,
                format String,
                materials String,
                birthdate Date,
                created_at DateTime,
                timestamp UInt64
            ) ENGINE = MergeTree()
            PARTITION BY toYYYYMM(created_at)
            ORDER BY (created_at, topic)",
            
            "CREATE TABLE IF NOT EXISTS lab6_stats.daily_stats (
                date Date,
                topic String,
                format String,
                registrations_count UInt32,
                materials_count UInt32
            ) ENGINE = SummingMergeTree()
            PARTITION BY toYYYYMM(date)
            ORDER BY (date, topic, format)"
        ];

        foreach ($queries as $query) {
            try {
                $this->client->post('', ['body' => $query]);
            } catch (Exception $e) {
                // Игнорируем ошибки "уже существует"
                if (strpos($e->getMessage(), 'already exists') === false) {
                    error_log("ClickHouse init error: " . $e->getMessage());
                }
            }
        }
    }

    // Сохранение данных регистрации для аналитики
    public function logRegistration($registrationData) {
        if (!$this->isConnected) return false;

        try {
            $query = "INSERT INTO lab6_stats.registrations VALUES (
                generateUUIDv4(),
                '{$this->escape($registrationData['name'])}',
                '{$this->escape($registrationData['email'])}',
                '{$this->escape($registrationData['topic'])}',
                '{$this->escape($registrationData['format'])}',
                '{$this->escape($registrationData['materials'])}',
                '{$registrationData['birthdate']}',
                now(),
                " . time() . "
            )";

            $response = $this->client->post('', ['body' => $query]);
            return $response->getStatusCode() === 200;
        } catch (Exception $e) {
            error_log("ClickHouse insert failed: " . $e->getMessage());
            return false;
        }
    }

    // Агрегированная статистика
    public function getAggregatedStats($days = 30) {
        if (!$this->isConnected) return [];

        try {
            $query = "
                SELECT 
                    toDate(created_at) as date,
                    topic,
                    format,
                    count() as registrations_count,
                    sumIf(1, materials = 'Да') as with_materials
                FROM lab6_stats.registrations 
                WHERE created_at >= now() - INTERVAL {$days} DAY
                GROUP BY date, topic, format
                ORDER BY date DESC, registrations_count DESC
            ";

            $response = $this->client->post('', [
                'body' => $query,
                'query' => ['default_format' => 'JSONCompact']
            ]);

            $data = json_decode($response->getBody(), true);
            return $this->formatClickHouseResponse($data);
        } catch (Exception $e) {
            error_log("ClickHouse query failed: " . $e->getMessage());
            return [];
        }
    }

    // Топ тем мастер-классов
    public function getPopularTopics($limit = 5) {
        if (!$this->isConnected) return [];

        try {
            $query = "
                SELECT 
                    topic,
                    count() as registrations_count,
                    round(count() * 100.0 / (SELECT count() FROM lab6_stats.registrations), 2) as percentage
                FROM lab6_stats.registrations 
                GROUP BY topic
                ORDER BY registrations_count DESC
                LIMIT {$limit}
            ";

            $response = $this->client->post('', [
                'body' => $query,
                'query' => ['default_format' => 'JSONCompact']
            ]);

            $data = json_decode($response->getBody(), true);
            return $this->formatClickHouseResponse($data);
        } catch (Exception $e) {
            error_log("ClickHouse topics query failed: " . $e->getMessage());
            return [];
        }
    }

    // Ежедневная статистика регистраций
    public function getDailyRegistrations($days = 7) {
        if (!$this->isConnected) return [];

        try {
            $query = "
                SELECT 
                    toDate(created_at) as date,
                    count() as registrations_count
                FROM lab6_stats.registrations 
                WHERE created_at >= now() - INTERVAL {$days} DAY
                GROUP BY date
                ORDER BY date
            ";

            $response = $this->client->post('', [
                'body' => $query,
                'query' => ['default_format' => 'JSONCompact']
            ]);

            $data = json_decode($response->getBody(), true);
            return $this->formatClickHouseResponse($data);
        } catch (Exception $e) {
            error_log("ClickHouse daily stats failed: " . $e->getMessage());
            return [];
        }
    }

    private function escape($value) {
        return str_replace("'", "''", $value);
    }

    private function formatClickHouseResponse($data) {
        if (empty($data['data'])) return [];
        
        $columns = $data['meta'] ?? [];
        $rows = $data['data'] ?? [];
        
        $result = [];
        foreach ($rows as $row) {
            $item = [];
            foreach ($columns as $index => $column) {
                $item[$column['name']] = $row[$index];
            }
            $result[] = $item;
        }
        
        return $result;
    }

    public function __get($property) {
        if ($property === 'isConnected') {
            return $this->isConnected;
        }
    }
}
?>