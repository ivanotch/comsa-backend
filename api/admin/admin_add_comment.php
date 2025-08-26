<?php
declare(strict_types=1);
require_once "../../config/db.php"; // adjust path
require_once "../../config/session.php";

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['post_id'], $input['comment'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$postId = (int)$input['post_id'];
$comment = trim($input['comment']);
$studentId = $_SESSION['user_id'] ?? null; // Assuming logged in user session

if (!$studentId) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO post_comments (post_id, student_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $studentId, $comment]);

    $commentId = (int)$pdo->lastInsertId();

    // Fetch student details for response
    $stmt = $pdo->prepare("SELECT name, profile_photo FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "comment_id" => $commentId,
        "student_name" => $student['name'],
        "student_photo" => $student['profile_photo'] ?? null
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
