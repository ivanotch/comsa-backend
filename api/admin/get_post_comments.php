<?php
require_once "../../config/db.php";

header("Content-Type: application/json");

$postId = $_GET['post_id'] ?? null;

if (!$postId) {
    echo json_encode(["success" => false, "message" => "Post ID required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT c.id, c.comment, s.name AS student_name, s.profile_photo AS student_photo 
                           FROM post_comments c
                           JOIN students s ON s.id = c.student_id
                           WHERE c.post_id = ?
                           ORDER BY c.created_at DESC");
    $stmt->execute([$postId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "comments" => $comments]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
