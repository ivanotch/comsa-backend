<?php
require_once '../../config/session.php';
require_once '../../config/db.php';
require_once '../../vendor/autoload.php';

use WebSocket\Client;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

header('Content-Type: application/json');
ini_set('display_errors', 0); // prevent PHP notices/warnings from breaking JSON

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$commentId = isset($data['id']) ? (int)$data['id'] : null;
$postId = isset($data['postId']) ? (int)$data['postId'] : null;

if (!$commentId) {
    echo json_encode(["success" => false, "message" => "Comment ID is required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM post_comments WHERE id = :id");
    $stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
    $stmt->execute();

    $response = [
        "success" => true,
        "message" => "Comment deleted successfully",
        "deleted_id" => $commentId
    ];

    $payload = json_encode([
        "type" => "adminDeleteComment",
        "message" => "Comment deleted successfully",
        "deleted_id" => $commentId,
        "postId" => $postId
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
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
