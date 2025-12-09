<?php
/**
 * ====================================
 * Database Connection Class
 * کلاس اتصال به دیتابیس با PDO
 * ====================================
 */

// بارگذاری فایل تنظیمات
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    private $stmt;
    
    /**
     * Constructor - اتصال به دیتابیس
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database Connection Error: " . $e->getMessage());
            } else {
                die("خطا در اتصال به دیتابیس. لطفاً بعداً تلاش کنید.");
            }
        }
    }
    
    /**
     * Singleton Pattern - یک نمونه از کلاس
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * دریافت اتصال
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * آماده‌سازی کوئری
     */
    public function prepare($sql) {
        $this->stmt = $this->connection->prepare($sql);
        return $this;
    }
    
    /**
     * اجرای کوئری با پارامترها
     */
    public function execute($params = []) {
        try {
            $this->stmt->execute($params);
            return $this;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Query Error: " . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * دریافت تمام نتایج
     */
    public function fetchAll() {
        return $this->stmt->fetchAll();
    }
    
    /**
     * دریافت یک ردیف
     */
    public function fetch() {
        return $this->stmt->fetch();
    }
    
    /**
     * دریافت تعداد ردیف‌ها
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * دریافت آخرین ID اضافه شده
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * شروع تراکنش
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * تایید تراکنش
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * لغو تراکنش
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * جلوگیری از کپی کردن
     */
    private function __clone() {}
    
    /**
     * جلوگیری از unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// ====================================
// توابع کمکی دیتابیس
// ====================================

/**
 * دریافت نمونه دیتابیس
 */
function db() {
    return Database::getInstance();
}

/**
 * اجرای کوئری SELECT ساده
 */
function dbQuery($sql, $params = []) {
    return db()->prepare($sql)->execute($params)->fetchAll();
}

/**
 * دریافت یک ردیف
 */
function dbQueryOne($sql, $params = []) {
    return db()->prepare($sql)->execute($params)->fetch();
}

/**
 * اجرای کوئری INSERT/UPDATE/DELETE
 */
function dbExecute($sql, $params = []) {
    return db()->prepare($sql)->execute($params)->rowCount();
}

/**
 * چک کردن وجود رکورد
 */
function dbExists($table, $where, $params = []) {
    $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
    $result = dbQueryOne($sql, $params);
    return $result['count'] > 0;
}

/**
 * شمارش رکوردها
 */
function dbCount($table, $where = '1=1', $params = []) {
    $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
    $result = dbQueryOne($sql, $params);
    return (int)$result['count'];
}

/**
 * دریافت یک رکورد با ID
 */
function dbGetById($table, $id) {
    $sql = "SELECT * FROM {$table} WHERE id = ? LIMIT 1";
    return dbQueryOne($sql, [$id]);
}

/**
 * حذف رکورد با ID
 */
function dbDeleteById($table, $id) {
    $sql = "DELETE FROM {$table} WHERE id = ?";
    return dbExecute($sql, [$id]);
}

/**
 * به‌روزرسانی رکورد
 */
function dbUpdate($table, $data, $where, $whereParams = []) {
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        $fields[] = "`{$key}` = ?";
        $values[] = $value;
    }
    
    $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE {$where}";
    $params = array_merge($values, $whereParams);
    
    return dbExecute($sql, $params);
}

/**
 * درج رکورد جدید
 */
function dbInsert($table, $data) {
    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');
    
    $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";
    
    dbExecute($sql, array_values($data));
    return db()->lastInsertId();
}

/**
 * Escape کردن رشته برای استفاده در LIKE
 */
function dbEscapeLike($string) {
    return str_replace(['%', '_'], ['\%', '\_'], $string);
}

/**
 * ساخت WHERE clause با شرایط متعدد
 */
function dbBuildWhere($conditions) {
    if (empty($conditions)) {
        return '1=1';
    }
    
    $parts = [];
    foreach ($conditions as $key => $value) {
        if (is_null($value)) {
            $parts[] = "`{$key}` IS NULL";
        } else {
            $parts[] = "`{$key}` = ?";
        }
    }
    
    return implode(' AND ', $parts);
}

// ====================================
// ✅ فایل اتصال دیتابیس آماده است
// ====================================
?>