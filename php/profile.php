<?php
header('Content-Type: application/json');


$manager = new MongoDB\Driver\Manager("mongodb+srv://mithunvasanthr:1234@guvi.ppdzoy0.mongodb.net/");

$userId = 123;
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

    $manager->executeBulkWrite('users', $bulk);

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully.'
    ]);
    exit;
}


$filter = ['userId' => $userId];
$options = [];

$query = new MongoDB\Driver\Query($filter, $options);
$cursor = $manager->executeQuery('users', $query);

$profile = current($cursor->toArray());
$profileData = $profile ? (array)$profile : [];

echo json_encode([
    'success' => true,
    'profile' => $profileData
]);
