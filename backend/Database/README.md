# Database Connection Guide

## Single Database Connection System

This system ensures your website uses only **ONE** database connection across all files, improving performance and resource usage.

## How to Use

### Method 1: Using the Singleton Pattern (Recommended)

```php
<?php
require_once 'backend/Database/db.php';

// Get the database connection
$db = Db::getInstance();
$connection = $db->getConnection();

// Use the connection
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
```

### Method 2: Using the Global Function (Simplest)

```php
<?php
require_once 'backend/Database/db.php';

// Get the database connection directly
$connection = getDB();

// Use the connection
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
```

### Method 3: Legacy Compatibility (For existing code)

```php
<?php
require_once 'backend/Database/db.php';

// This still works but creates a new instance
$db = new Db();
$connection = $db->connect();

// Use the connection
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
```

## Benefits

✅ **Single Connection**: Only one database connection is created and reused
✅ **Better Performance**: Reduced connection overhead
✅ **Resource Efficient**: Less memory and CPU usage
✅ **Connection Pooling**: Better server resource management
✅ **Backward Compatible**: Existing code continues to work

## Migration Steps

1. **Update your includes**: Make sure all files include `backend/Database/db.php`
2. **Replace multiple connections**: Use `Db::getInstance()` or `getDB()` instead of `new Db()`
3. **Test thoroughly**: Ensure all database operations work correctly

## Example Migration

### Before (Multiple Connections):

```php
// File 1
$db1 = new Db();
$connection1 = $db1->connect();

// File 2
$db2 = new Db();
$connection2 = $db2->connect();

// File 3
$db3 = new Db();
$connection3 = $db3->connect();
```

### After (Single Connection):

```php
// File 1
$connection1 = getDB();

// File 2
$connection2 = getDB();

// File 3
$connection3 = getDB();

// All three variables point to the SAME connection!
```

## Important Notes

- The connection is automatically managed
- No need to manually close connections
- The system handles connection errors gracefully
- UTF-8 support is enabled by default
- Prepared statements are recommended for security
