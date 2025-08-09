<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config/session.php';
header('Content-Type: application/json');

if (!isset($_GET['project_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing project ID']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized Access'
    ]);
    exit;
}

$projectId = $_GET['project_id'];

require_once '../config/db.php';
require_once '../model/get_comment_model.php';

try {
    $result = get_comments($pdo, $projectId);

    echo json_encode($result);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to fetch comments.']);
}

