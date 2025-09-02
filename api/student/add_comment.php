<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = $data['post_id'] ?? null;
$comment = $data['comment'] ?? null;
$studentId = $_SESSION['user_id'];

if (!$postId || !$comment) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID and comment required']);
    exit;
}

try {
    // Insert comment
    $stmt = $pdo->prepare("INSERT INTO post_comments (post_id, student_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $studentId, $comment]);
    
    // Get student name for response
    $studentStmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
    $studentStmt->execute([$studentId]);
    $student = $studentStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'student_name' => $student['name']
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>