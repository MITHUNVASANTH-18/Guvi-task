<?php
$host = "guvi.cz8ugi66ap5w.eu-north-1.rds.amazonaws.com";
$dbname = "guvi";
$username = "admin";
$password = "Admin123";

$redis = new Redis();
$redis->connect('13.61.176.93', 6379);

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

        $redisKey = "$token";
        $redis->set($redisKey, $userId);
        $redis->expire($redisKey, 3600); 


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
