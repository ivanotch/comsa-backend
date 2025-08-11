<?php

require_once '../config/session.php';
require_once '../config/db.php';
require_once '../model/get_avatar_model.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized Access'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

try {

    $filepath = get_user_avatar($pdo, $userId);

    if ($filepath) {
        // If no profile photo, return a default avatar path
        $avatarPath = !empty($filepath['profile_photo'])
            ? $filepath['profile_photo']
            : null; // adjust path if needed

        echo json_encode([
            'success' => true,
            'filepath' => $avatarPath
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
