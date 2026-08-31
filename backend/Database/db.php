<?php
// Database connection class for ATMABISWAS
// Updated to use Singleton pattern for single database connection

class Db
{
    // New lines added
    private static $instance = null;
    private $hostname = "localhost";
    private $user = "";
    private $pswd = "";
    private $dbname = "";
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
        // Load credentials from a private config file that lives ONLY on the
        // server and is never committed to git (see db.config.example.php).
        $configFile = __DIR__ . '/db.config.php';
        if (is_readable($configFile)) {
            $config = require $configFile;
            if (is_array($config)) {
                if (!empty($config['host'])) $this->hostname = $config['host'];
                if (!empty($config['user'])) $this->user     = $config['user'];
                if (isset($config['pass']))  $this->pswd     = $config['pass'];
                if (!empty($config['name'])) $this->dbname   = $config['name'];
            }
        }

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

/**
 * Columns img_upload actually has, as a lookup set.
 *
 * The table has drifted between installs: some carry img_title/img_description,
 * others only img_name, and display_order is added on demand by the upload
 * handler. imageSlider.php has always adapted its SELECT this way; the write
 * paths did not, so they inserted columns the live table does not have and the
 * PDOException surfaced to the admin as a bare "File upload failed."
 *
 * Deliberately not cached: the upload handler may ALTER the table to add
 * display_order moments before writing, and a cached list would miss it.
 */
function img_upload_columns(PDO $pdo): array
{
    return array_flip($pdo->query("SHOW COLUMNS FROM img_upload")->fetchAll(PDO::FETCH_COLUMN));
}

/**
 * Column => value map for an img_upload write, restricted to columns that exist.
 *
 * img_name is NOT NULL on the installs that carry it and has no default, so the
 * title doubles as the name there; installs with img_title keep title and
 * description separate. img_path is the only column assumed to always exist.
 */
function img_upload_payload(
    PDO $pdo,
    string $title,
    string $description,
    string $path,
    string $type,
    int $order
): array {
    $cols = img_upload_columns($pdo);
    $data = ['img_path' => $path];

    if (isset($cols['img_title']))       $data['img_title']       = $title;
    if (isset($cols['img_description'])) $data['img_description'] = $description;
    if (isset($cols['img_name']))        $data['img_name']        = $title;
    if (isset($cols['img_type']))        $data['img_type']        = $type;
    if (isset($cols['display_order']))   $data['display_order']   = $order;

    return $data;
}

/** Bind a payload built by img_upload_payload(), typing display_order as int. */
function img_upload_bind(PDOStatement $stmt, array $payload): void
{
    foreach ($payload as $column => $value) {
        $stmt->bindValue(
            ':' . $column,
            $value,
            $column === 'display_order' ? PDO::PARAM_INT : PDO::PARAM_STR
        );
    }
}
