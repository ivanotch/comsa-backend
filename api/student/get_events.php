<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Get active and upcoming events (not ended) - exclude event_image
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            start_date,
            end_date,
            status
        FROM events 
        WHERE status IN ('active', 'upcoming') 
        AND end_date >= NOW()
        ORDER BY start_date ASC
        LIMIT 5
    ");
    
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'events' => $events
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in get_events.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>