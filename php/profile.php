<?php
header('Content-Type: application/json');
require 'vendor/autoload.php';

use MongoDB\Client;

$mongo = new Client("mongodb+srv://mithunvasanthr:1234@guvi.ppdzoy0.mongodb.net/");
$collection = $mongo->your_mongo_db->profiles;

$userId = 123; // Hardcoded for testing
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = $input['age'] ?? null;
    $dob = $input['dob'] ?? null;
    $contact = $input['contact'] ?? null;

    $updateResult = $collection->updateOne(
        ['user_id' => $userId],
        ['$set' => [
            'age' => $age,
            'dob' => $dob,
            'contact' => $contact
        ]],
        ['upsert' => true]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully.'
    ]);
    exit;
}

// GET request: return profile
$profile = $collection->findOne(['user_id' => $userId]);
$profileData = $profile ? $profile->getArrayCopy() : [];

echo json_encode([
    'success' => true,
    'profile' => $profileData
]);
exit;
