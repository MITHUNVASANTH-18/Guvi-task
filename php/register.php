<?php
header('Content-Type: application/json');

$mysql_host = "guvi.cz8ugi66ap5w.eu-north-1.rds.amazonaws.com";
$mysql_db = "guvi";
$mysql_user = "admin";
$mysql_pass =  "Admin123";

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

    $manager = new MongoDB\Driver\Manager("mongodb+srv://mithunvasanthr:1234@guvi.ppdzoy0.mongodb.net/"); 
    $bulk = new MongoDB\Driver\BulkWrite;

    $profileDoc = [
        'userId' => (int)$user_id,
        'age' => null,
        'dob' => null,
        'contact' => null,
    ];
    $bulk->insert($profileDoc);


    $writeResult = $manager->executeBulkWrite('mydb.users', $bulk);

    if ($writeResult->getInsertedCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'MongoDB profile creation failed.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
