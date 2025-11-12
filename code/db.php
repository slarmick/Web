<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = 'db';
        $db   = 'lab6_db';  // ← ИЗМЕНИТЬ на lab6_db
        $user = 'lab6_user'; // ← ИЗМЕНИТЬ на lab6_user  
        $pass = 'lab6_pass'; // ← ИЗМЕНИТЬ на lab6_pass
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

// Глобальная функция для удобства
function getDB() {
    return Database::getInstance()->getConnection();
}
?>