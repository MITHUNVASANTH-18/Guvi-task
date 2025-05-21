<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

header('Content-Type: application/json');


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$mysql_host = $_ENV['MYSQL_HOST'];
$mysql_db = $_ENV['MYSQL_DB'];
$mysql_user = $_ENV['MYSQL_USER'];
$mysql_pass = $_ENV['MYSQL_PASS'];
$mongo_uri = $_ENV['MONGO_URI'];
$mongo_db = $_ENV['MONGO_DB'];
$mongo_collection = $_ENV['MONGO_COLLECTION'];

try {

    $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_db;charset=utf8mb4", $mysql_user, $mysql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (!$name || !$email || !$password) {
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
    $stmt->execute([$name, $email, $passwordHash]);

    $userId = (int)$pdo->lastInsertId();


    $manager = new MongoDB\Driver\Manager($mongo_uri);

    $bulk = new MongoDB\Driver\BulkWrite;
    $profileDoc = [
        'userId' => $userId,
        'name' => $name,
        'email' => $email,
        'age' => null,
        'dob' => null,
        'contact' => null,
    ];
    $bulk->insert($profileDoc);

    $writeResult = $manager->executeBulkWrite("$mongo_db.$mongo_collection", $bulk);

    if ($writeResult->getInsertedCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create user profile in MongoDB.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
