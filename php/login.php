<?php

header('Content-Type: application/json');


$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['username'], $input['email'], $input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];

if (strlen($username) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Validation failed']);
    exit;
}

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

    $filter = ['$or' => [
        ['username' => $username],
        ['email' => $email]
    ]];

    $query = new MongoDB\Driver\Query($filter);
    $cursor = $manager->executeQuery('mydb.users', $query);
    $existingUsers = $cursor->toArray();

    if (count($existingUsers) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $bulk = new MongoDB\Driver\BulkWrite;
    $doc = [
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    $bulk->insert($doc);

    $result = $manager->executeBulkWrite('mydb.users', $bulk);

    echo json_encode(['success' => true]);

} catch (MongoDB\Driver\Exception\Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
