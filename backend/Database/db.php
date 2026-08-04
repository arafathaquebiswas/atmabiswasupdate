<?php
// Database connection class for ATMABISWAS
// Updated to use Singleton pattern for single database connection

class Db
{
    // New lines added
    private static $instance = null;
    private $hostname = "localhost";
    private $user = "u106340611_arafat";
    private $pswd = "MacBook@007Arafat";
    private $dbname = "u106340611_arafatbiswas";
    private $pdo;

    // Public constructor for backward compatibility
    public function __construct()
    {
        $this->initializeConnection();
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    // Get the single instance of the database connection
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Check system requirements for database connection
    public static function checkRequirements()
    {
        $requirements = [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mysql_attr_init_command' => defined('Pdo\\Mysql::ATTR_INIT_COMMAND') || defined('PDO::MYSQL_ATTR_INIT_COMMAND')
        ];

        return $requirements;
    }

    // Initialize the database connection
    private function initializeConnection()
    {
        // Allow environment variables to override default credentials
        if (getenv('DB_HOST')) $this->hostname = getenv('DB_HOST');
        if (getenv('DB_USER')) $this->user     = getenv('DB_USER');
        if (getenv('DB_PASS') !== false) $this->pswd = getenv('DB_PASS');
        if (getenv('DB_NAME')) $this->dbname   = getenv('DB_NAME');

        // Check if PDO MySQL extension is loaded
        if (!extension_loaded('pdo_mysql')) {
            throw new Exception("PDO MySQL extension is not loaded. Please enable it in your PHP configuration.");
        }

        // Build PDO options array safely (handling PHP 8.5+ deprecations)
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        if (defined('Pdo\\Mysql::ATTR_INIT_COMMAND')) {
            $options[\Pdo\Mysql::ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
        } elseif (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            @$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
        }

        try {
            $this->pdo = new PDO(
                "mysql:host=$this->hostname;dbname=$this->dbname;charset=utf8mb4",
                $this->user,
                $this->pswd,
                $options
            );
        } catch (PDOException $e) {
            // Secondary fallback for local dev environments where root user with empty password might be used
            if ($this->hostname === 'localhost' || $this->hostname === '127.0.0.1') {
                try {
                    $this->pdo = new PDO(
                        "mysql:host=$this->hostname;dbname=$this->dbname;charset=utf8mb4",
                        "root",
                        "",
                        $options
                    );
                    return;
                } catch (PDOException $e2) {
                    // Fallback failed as well, log original error
                }
            }

            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }

    // Get the PDO connection
    public function getConnection()
    {
        return $this->pdo;
    }

    // Legacy method for backward compatibility
    public function connect()
    {
        return $this->pdo;
    }

    // Close the database connection
    public function closeConnection()
    {
        $this->pdo = null;
        self::$instance = null;
    }

    // Get connection status
    public function isConnected()
    {
        return $this->pdo !== null;
    }
}

// Global function for easy access to database connection
function getDB()
{
    return Db::getInstance()->getConnection();
}
