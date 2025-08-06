<?php

require_once '../config/session.php';
require_once '../config/db.php'; // Adjust path as needed
require_once '../model/add_comment_model.php';
//require an add user
require_once '../vendor/autoload.php';

use WebSocket\Client;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$studentId = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$projectId = $data['project_id'] ?? null;
$comment = trim($data['comment'] ?? "");

if (!$projectId || $comment === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid Input']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO project_comments (project_id, student_id, comment) VALUES (?, ?, ?)");
$stmt->execute([$projectId, $studentId, $comment]);

$commentCountStmt = $pdo->prepare("SELECT COUNT(*) FROM project_comments WHERE project_id = ?");
$commentCountStmt->execute([$projectId]);
$commentCount = (int)$commentCountStmt->fetchColumn();

$createdAt = date('Y-m-d H:i:s');
$studentName = get_student_name($pdo, $studentId); 

$response = [
    'success' => true,
    'comment' => [
        'student_id' => $studentId,
        'name' => $studentName,
        'comment' => $comment,
        'created_at' => $createdAt
    ],
    'comment_count' => $commentCount
];

//send to webSocket
$payload = json_encode([
    'type' => 'comment',
    'name' => $studentName,
    'project_id' => $projectId,
    'comment' => $comment, // <- just the string now
    'student_id' => $studentId,
    'comment_count' => $commentCount
]);

try {
    $client = new Client("ws://127.0.0.1:8080");
    $client
        ->addMiddleware(new CloseHandler())
        ->addMiddleware(new PingResponder());

    $client->text($payload);
    $client->close();
} catch (Exception $e) {
    error_log("WebSocket client error: " . $e->getMessage());
}

echo json_encode($response);

