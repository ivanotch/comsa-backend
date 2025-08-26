<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

header('Content-Type: application/json');
ini_set('display_errors', 0); // prevent PHP notices/warnings from breaking JSON

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$commentId = isset($data['id']) ? (int)$data['id'] : null;

if (!$commentId) {
    echo json_encode(["success" => false, "message" => "Comment ID is required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM post_comments WHERE id = :id");
    $stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Comment deleted successfully",
        "deleted_id" => $commentId
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
