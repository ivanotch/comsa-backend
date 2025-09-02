<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $studentId = $_SESSION['user_id'];
    
    // Get all published posts with like and comment info
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.title,
            p.post_image,
            p.content,
            p.created_at,
            p.updated_at,
            a.name AS admin_username,
            COUNT(DISTINCT pl.id) AS like_count,
            COUNT(DISTINCT pc.id) AS comment_count,
            GROUP_CONCAT(DISTINCT t.tag_name) AS tags,
            EXISTS(
                SELECT 1 FROM post_likes 
                WHERE post_id = p.id AND student_id = :student_id
            ) AS user_liked
        FROM admin_post p
        LEFT JOIN students a ON p.admin_id = a.id
        LEFT JOIN post_likes pl ON p.id = pl.post_id
        LEFT JOIN post_comments pc ON p.id = pc.post_id
        LEFT JOIN admin_tags t ON p.id = t.post_id
        WHERE p.post_status = 'published'
        GROUP BY p.id, a.name
        ORDER BY p.created_at DESC
    ");
    
    $stmt->execute([':student_id' => $studentId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get comments for each post
    foreach ($posts as &$post) {
        $commentStmt = $pdo->prepare("
            SELECT pc.comment, s.name AS student_name
            FROM post_comments pc
            JOIN students s ON pc.student_id = s.id
            WHERE pc.post_id = :post_id
            ORDER BY pc.created_at DESC
            LIMIT 5
        ");
        
        $commentStmt->execute([':post_id' => $post['id']]);
        $post['comments'] = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert tags string to array
        $post['tags'] = $post['tags'] ? explode(',', $post['tags']) : [];
    }
    
    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>