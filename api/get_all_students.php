<?php
require_once '../config/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

try {
$stmt = $pdo->prepare("
    SELECT 
        s.id, 
        s.name as username, 
        s.profile_photo as profile_picture,
        s.nickname,
        s.student_number,
        COUNT(DISTINCT p.id) as total_projects,
        COALESCE((
            SELECT COUNT(*) 
            FROM project_likes pl 
            JOIN projects p2 ON pl.project_id = p2.id 
            WHERE p2.student_id = s.id
        ), 0) as total_stars
    FROM students s
    LEFT JOIN projects p ON s.id = p.student_id
    GROUP BY s.id, s.name, s.profile_photo, s.nickname, s.student_number
    ORDER BY s.name ASC
");
    
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
    
} catch (PDOException $e) {
    error_log('Get all students error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>