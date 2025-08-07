<?php

require_once '../config/session.php';
require_once '../config/db.php';

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

    // $filepath = get_user_avatar();


} catch (PDOException $e) {

}