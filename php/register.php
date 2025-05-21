<?php
header('Content-Type: application/json');

$mysql_host = "guvi.cz8ugi66ap5w.eu-north-1.rds.amazonaws.com";
$mysql_db = "guvi";
$mysql_user = "admin";
$mysql_pass = "Admin123";

try {
    $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_db;charset=utf8mb4", $mysql_user, $mysql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$email || !$password || !$username) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        exit;
    }

 
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $passwordHash]);

    $userId = $pdo->lastInsertId();


    $manager = new MongoDB\Driver\Manager("mongodb+srv://mithunvasanthr:1234@guvi.ppdzoy0.mongodb.net/");

    $bulk = new MongoDB\Driver\BulkWrite;
    $profileDoc = [
        'userId' => (int)$userId,
        'username' => $username,
        'email' => $email,
        'age' => null,
        'dob' => null,
        'contact' => null,
    ];
    $bulk->insert($profileDoc);
    $manager->executeBulkWrite('mydb.users', $bulk);

    echo json_encode(['success' => true, 'message' => 'Registration successful.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
