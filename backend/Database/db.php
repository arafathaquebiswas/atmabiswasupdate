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
 * Add the columns the Latest News form writes, where this install lacks them.
 *
 * Installs created from hostinger_import.sql carry img_name but no
 * img_description, so a description typed into the form was silently dropped by
 * img_upload_payload(). Latest News now requires a description, so the column
 * has to exist. fb_link holds the optional Facebook post for the same story,
 * press_link the optional full article on the Press page.
 * (Database/migrations/add_press_link_to_img_upload.sql adds the same column.)
 */
function img_upload_ensure_columns(PDO $pdo): void
{
    $cols = img_upload_columns($pdo);
    if (!isset($cols['img_description'])) {
        $pdo->exec("ALTER TABLE img_upload ADD COLUMN img_description TEXT NULL");
    }
    if (!isset($cols['fb_link'])) {
        $pdo->exec("ALTER TABLE img_upload ADD COLUMN fb_link VARCHAR(500) NULL");
    }
    if (!isset($cols['press_link'])) {
        $pdo->exec("ALTER TABLE img_upload ADD COLUMN press_link VARCHAR(500) NULL");
    }
}

/**
 * Validate the optional Facebook link of a Latest News post.
 *
 * Returns null for an empty field, the trimmed URL for an http(s) link on a
 * Facebook domain, and false otherwise. The host check keeps a mistyped or
 * unrelated address off the public card; the scheme check keeps javascript:
 * URLs out of the href. A bare https://www.facebook.com/ is refused as well:
 * the card's button promises that story's post, not Facebook's front page.
 */
function img_upload_fb_link(string $raw): string|false|null
{
    $url = trim($raw);
    if ($url === '') {
        return null;
    }
    if (strlen($url) > 500 || preg_match('/\s/', $url)) {
        return false;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    $host   = strtolower((string) parse_url($url, PHP_URL_HOST));
    $path   = (string) parse_url($url, PHP_URL_PATH);
    $query  = (string) parse_url($url, PHP_URL_QUERY);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return false;
    }
    if (trim($path, '/') === '' && $query === '') {
        return false;
    }
    return preg_match('/(^|\.)(facebook\.com|fb\.com|fb\.watch)$/', $host) ? $url : false;
}

/**
 * Validate the optional Press article link of a Latest News post.
 *
 * Accepts a link to one article on this site's Press page in any form an admin
 * is likely to paste -- "press.php?id=12", "/press.php?id=12" or the full
 * https://atmabiswas.org/press.php?id=12 -- and returns it normalised to
 * "press.php?id=12", the form test.php renders. Returns null for an empty field
 * and false for anything else: another site, another page, a javascript: URL,
 * or an id with no article behind it.
 */
function img_upload_press_link(PDO $pdo, string $raw): string|false|null
{
    $url = trim($raw);
    if ($url === '') {
        return null;
    }
    if (strlen($url) > 500 || preg_match('/\s/', $url)) {
        return false;
    }
    $parts = parse_url($url);
    if ($parts === false) {
        return false;
    }
    if (isset($parts['scheme']) || isset($parts['host'])) {
        $scheme = strtolower($parts['scheme'] ?? '');
        $host   = strtolower($parts['host'] ?? '');
        // This site's own host too, so a link copied from a local or staging copy works.
        $own    = ['atmabiswas.org', 'www.atmabiswas.org',
                   strtolower((string) parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST))];
        if (!in_array($scheme, ['http', 'https'], true) || !in_array($host, $own, true)) {
            return false;
        }
    }
    if (!preg_match('~^/?press\.php$~', $parts['path'] ?? '')) {
        return false;
    }
    parse_str($parts['query'] ?? '', $query);
    $id = $query['id'] ?? '';
    if (!is_string($id) || !preg_match('/^[1-9][0-9]{0,9}$/', $id)) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE blog_id = ?");
    $stmt->execute([(int) $id]);
    return (int) $stmt->fetchColumn() === 1 ? 'press.php?id=' . (int) $id : false;
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
    int $order,
    ?string $fbLink = null,
    ?string $pressLink = null
): array {
    $cols = img_upload_columns($pdo);
    $data = ['img_path' => $path];

    if (isset($cols['img_title']))       $data['img_title']       = $title;
    if (isset($cols['img_description'])) $data['img_description'] = $description;
    if (isset($cols['img_name']))        $data['img_name']        = $title;
    if (isset($cols['img_type']))        $data['img_type']        = $type;
    if (isset($cols['display_order']))   $data['display_order']   = $order;
    if (isset($cols['fb_link']))         $data['fb_link']         = $fbLink;
    if (isset($cols['press_link']))      $data['press_link']      = $pressLink;

    return $data;
}

/** Bind a payload built by img_upload_payload(): display_order as int, null as NULL. */
function img_upload_bind(PDOStatement $stmt, array $payload): void
{
    foreach ($payload as $column => $value) {
        $stmt->bindValue(
            ':' . $column,
            $value,
            $value === null ? PDO::PARAM_NULL
                : ($column === 'display_order' ? PDO::PARAM_INT : PDO::PARAM_STR)
        );
    }
}
