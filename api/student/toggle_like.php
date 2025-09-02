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
$studentId = $_SESSION['user_id'];

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit;
}

try {
    // Check if already liked
    $checkStmt = $pdo->prepare("SELECT id FROM post_likes WHERE post_id = ? AND student_id = ?");
    $checkStmt->execute([$postId, $studentId]);
    $existingLike = $checkStmt->fetch();
    
    if ($existingLike) {
        // Unlike
        $deleteStmt = $pdo->prepare("DELETE FROM post_likes WHERE id = ?");
        $deleteStmt->execute([$existingLike['id']]);
        $liked = false;
    } else {
        // Like
        $insertStmt = $pdo->prepare("INSERT INTO post_likes (post_id, student_id) VALUES (?, ?)");
        $insertStmt->execute([$postId, $studentId]);
        $liked = true;
    }
    
    // Get updated like count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM post_likes WHERE post_id = ?");
    $countStmt->execute([$postId]);
    $likeCount = $countStmt->fetch()['like_count'];
    
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'like_count' => $likeCount
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>