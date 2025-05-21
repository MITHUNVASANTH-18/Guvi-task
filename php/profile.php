<?php
header('Content-Type: application/json');

$redis = new Redis();
try {
    $redis->connect('13.61.176.93', 6379);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Redis connection failed']);
    exit;
}


$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(['success' => false, 'message' => 'Authorization token missing']);
    exit;
}

$token = $matches[1];

$userId = $redis->get($token);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit;
}

$userId = (int)$userId; 


try {
    $manager = new MongoDB\Driver\Manager("mongodb+srv://mithunvasanthr:1234@guvi.ppdzoy0.mongodb.net/");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'MongoDB connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = $input['age'] ?? null;
    $dob = $input['dob'] ?? null;
    $contact = $input['contact'] ?? null;

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['userId' => $userId],
        ['$set' => [
            'age' => $age,
            'dob' => $dob,
            'contact' => $contact
        ]],
        ['upsert' => true]
    );

    try {
        $manager->executeBulkWrite('mydb.users', $bulk);
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'MongoDB write failed: ' . $e->getMessage()]);
    }
    exit;
}


$filter = ['userId' => $userId];
$options = [];

$query = new MongoDB\Driver\Query($filter, $options);

try {
    $cursor = $manager->executeQuery('mydb.users', $query);
    $profile = current($cursor->toArray());
    $profileData = $profile ? (array)$profile : [];

    echo json_encode([
        'success' => true,
        'profile' => $profileData
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'MongoDB query failed: ' . $e->getMessage()]);
}
exit;
