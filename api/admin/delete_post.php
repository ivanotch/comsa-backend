<?php
require_once "../../config/session.php"; 
require_once "../../config/db.php";   

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$postId = $data["postId"] ?? null;

if (!$postId) {
    echo json_encode(["success" => false, "message" => "No post ID provided"]);
    exit;
}

try {
    $pdo->prepare("DELETE FROM post_comments WHERE post_id = ?")->execute([$postId]);
    $pdo->prepare("DELETE FROM post_likes WHERE post_id = ?")->execute([$postId]);

    // Then delete the post itself
    $stmt = $pdo->prepare("DELETE FROM admin_post WHERE id = ?");
    $stmt->execute([$postId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Post deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Post not found"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
