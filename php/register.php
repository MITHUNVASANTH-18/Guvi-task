<?php
header('Content-Type: application/json');
require 'vendor/autoload.php'; /


$mysql_host = "localhost";
$mysql_db = "your_mysql_db";
$mysql_user = "your_mysql_user";
$mysql_pass = "your_mysql_pass";

try {

    $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_db;charset=utf8mb4", $mysql_user, $mysql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $name = $data['name'] ?? '';

    if (!$email || !$password || !$name) {
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

    $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (?, ?, ?)");
    $stmt->execute([$email, $passwordHash, $name]);

    $user_id = $pdo->lastInsertId();

    $mongoClient = new MongoDB\Client("mongodb+srv://mithunvasanthr:1234@guvi.ppdzoy0.mongodb.net/");
    $profilesCollection = $mongoClient->yourMongoDbName->profiles;

    $profileDoc = [
        'user_id' => (int)$user_id,
        'age' => null,
        'dob' => null,
        'contact' => null,
        'additional_info' => new stdClass()
    ];

    $insertResult = $profilesCollection->insertOne($profileDoc);

    if ($insertResult->getInsertedCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create profile document.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
