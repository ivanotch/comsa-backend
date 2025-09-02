<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Check if events table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'events'");
    $eventsTableExists = $stmt->rowCount() > 0;
    
    // Check if quick_links table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'quick_links'");
    $quickLinksTableExists = $stmt->rowCount() > 0;
    
    echo json_encode([
        'success' => true,
        'tables' => [
            'events' => $eventsTableExists,
            'quick_links' => $quickLinksTableExists
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>