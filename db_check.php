<?php
// TEMPORARY DIAGNOSTIC — DELETE THIS FILE AFTER USE.
// Reports why the database layer is failing, without revealing the password.
header('Content-Type: text/plain; charset=utf-8');

echo "== ATMABISWAS DB diagnostic ==\n\n";

$cfg = __DIR__ . '/backend/Database/db.config.php';
echo "1) db.config.php exists?      " . (is_readable($cfg) ? "YES" : "NO  <-- create it") . "\n";

$conf = is_readable($cfg) ? require $cfg : [];
if (is_array($conf)) {
    echo "   host = " . ($conf['host'] ?? '(unset)') . "\n";
    echo "   user = " . ($conf['user'] ?? '(unset)') . "\n";
    echo "   name = " . ($conf['name'] ?? '(unset)') . "\n";
    echo "   pass = " . (empty($conf['pass']) ? '(EMPTY!)' : '(set, ' . strlen($conf['pass']) . ' chars)') . "\n";
}
echo "\n";

echo "2) pdo_mysql loaded?          " . (extension_loaded('pdo_mysql') ? "YES" : "NO") . "\n\n";

try {
    require __DIR__ . '/backend/Database/db.php';
    $pdo = (new Db())->connect();
    echo "3) Connection:               OK\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "4) Tables in database:       " . count($tables) . "\n";
    if ($tables) echo "   " . implode(', ', $tables) . "\n";
    if (in_array('admins', array_map('strtolower', $tables))) {
        $n = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        echo "5) Rows in admins:           $n\n";
        foreach ($pdo->query("SELECT email, role FROM admins") as $r) {
            echo "   - {$r['email']} ({$r['role']})\n";
        }
    } else {
        echo "5) admins table:             MISSING  <-- import hostinger_import.sql\n";
    }
} catch (Throwable $e) {
    echo "3) Connection FAILED:\n   " . $e->getMessage() . "\n";
    echo "\n   -> wrong credentials in db.config.php, or the database is empty.\n";
}
echo "\n== end ==\n";
