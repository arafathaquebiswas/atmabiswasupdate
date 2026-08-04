<?php
$dsn = 'mysql:host=localhost;dbname=test_db';
$username = 'root';
$password = '';

try {

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



    // SQL query with placeholders
    $sql = 'SELECT id, username, email FROM users WHERE username = :username AND email = :email';

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Data to bind
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);

    // Bind the parameters
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);

    // Execute the statement
    $stmt->execute();

    // Fetch the results
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        echo 'ID: ' . $user['id'] . '<br>';
        echo 'Username: ' . $user['username'] . '<br>';
        echo 'Email: ' . $user['email'] . '<br><br>';
    }

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
