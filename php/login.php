<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

$redisHost = $_ENV['REDIS_HOST'];
$redisPort = $_ENV['REDIS_PORT'];

$redis = new Redis();
$redis->connect($redisHost, $redisPort);


$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}


$email = $_POST['email'] ?? '';
$pass = $_POST['password'] ?? '';

if (!$email || !$pass) {
    echo json_encode(["error" => "All fields are required."]);
    exit();
}

$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(["error" => "Invalid email or password."]);
} else {
    $stmt->bind_result($userId, $hashed);
    $stmt->fetch();
    if (password_verify($pass, $hashed)) {
        $token = bin2hex(random_bytes(16));

        $redis->set($token, $userId);
        $redis->expire($token, 3600); 

        echo json_encode([
            "status" => "success",
            "token" => $token,
        ]);
    } else {
        echo json_encode(["error" => "Invalid email or password."]);
    }
}

$stmt->close();
$conn->close();
?>
