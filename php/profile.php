<?php
header('Content-Type: application/json');

require 'vendor/autoload.php'; 


$client = new MongoDB\Client("mongodb+srv://mithunvasanthr:1234@guvi.ppdzoy0.mongodb.net/");

$collection = $client->your_mongo_db->profiles;


$userId = 123;

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update test profile data
    $age = $input['age'] ?? null;
    $dob = $input['dob'] ?? null;
    $contact = $input['contact'] ?? null;

    $updateResult = $collection->updateOne(
        ['user_id' => (int)$userId],
        ['$set' => [
            'age' => $age,
            'dob' => $dob,
            'contact' => $contact
        ]],
        ['upsert' => true]
    );

    if ($updateResult->getModifiedCount() > 0 || $updateResult->getUpsertedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed']);
    }
    exit;
}

// GET request: Fetch profile for hardcoded user id
$mongoProfile = $collection->findOne(['user_id' => (int)$userId]);
if (!$mongoProfile) {
    $mongoProfile = [];
} else {
    $mongoProfile = $mongoProfile->getArrayCopy();
}

echo json_encode([
    'success' => true,
    'userId' => $userId,
    'mongoProfile' => $mongoProfile
]);
