<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Get all quick links (your table doesn't have status field)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            url,
            category,
            remix_icon
        FROM quick_links 
        ORDER BY created_at DESC
        LIMIT 5
    ");
    
    $stmt->execute();
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'links' => $links
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in get_quick_links.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>