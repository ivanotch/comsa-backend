<?php
require_once '../config/session.php';
require_once '../config/db.php';
header('Content-Type: application/json');
require_once '../vendor/autoload.php';
require_once '../model/like_project_model.php';

use WebSocket\Client;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$studentId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$projectId = $data['project_id'] ?? null;

if (!$projectId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Check if already liked
$stmt = $pdo->prepare("SELECT 1 FROM project_likes WHERE project_id = ? AND student_id = ?");
$stmt->execute([$projectId, $studentId]);
$liked = $stmt->fetchColumn();


if ($liked) {
    // Unlike
    $stmt = $pdo->prepare("DELETE FROM project_likes WHERE project_id = ? AND student_id = ?");
    $stmt->execute([$projectId, $studentId]);

    $like_count = get_like_count($pdo, $projectId);

    $payload = json_encode([
        'type' => 'like',
        'status' => 'unliked',
        'project_id' => $projectId,
        'studentId' => $studentId,
        'like_count' => $like_count
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

    echo json_encode(['status' => 'unliked']);
} else {
    // Like
    $stmt = $pdo->prepare("INSERT INTO project_likes (project_id, student_id) VALUES (?, ?)");
    $stmt->execute([$projectId, $studentId]);

    $like_count = get_like_count($pdo, $projectId);

    $payload = json_encode([
        'type' => 'like',
        'status' => 'liked',
        'project_id' => $projectId,
        'studentId' => $studentId,
        'like_count' => $like_count
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

    echo json_encode(['status' => 'liked']);
}
