<?php
require_once "../../config/session.php";  // if you need session check
require_once "../../config/db.php";       // DB connection

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
    
    $stmt = $pdo->prepare("UPDATE admin_post SET post_status = 'archived' WHERE id = ?");
    $stmt->execute([$postId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Post archived successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Post not found or already archived"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
