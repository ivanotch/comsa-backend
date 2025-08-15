<?php
require_once '../config/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized Access'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

require_once '../config/db.php';

try {
    // Get projects for this specific student with all related data
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            COUNT(DISTINCT pl.id) AS like_count,
            COUNT(DISTINCT pc.id) AS comment_count,
            s.name AS student_name,
            s.profile_photo
        FROM projects p
        LEFT JOIN project_likes pl ON p.id = pl.project_id
        LEFT JOIN project_comments pc ON p.id = pc.project_id
        JOIN students s ON p.student_id = s.id
        WHERE p.student_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalStars = 0;
    $projectCount = count($projects);

    // Get additional project details for each project
    foreach ($projects as &$project) {
        // Fetch technologies
        $stmtTech = $pdo->prepare("SELECT technology_name FROM project_technologies WHERE project_id = ?");
        $stmtTech->execute([$project['id']]);
        $project['technologies'] = $stmtTech->fetchAll(PDO::FETCH_COLUMN);

        // Fetch team members
        $stmtMembers = $pdo->prepare("SELECT member_name FROM project_team_members WHERE project_id = ?");
        $stmtMembers->execute([$project['id']]);
        $project['team_members'] = $stmtMembers->fetchAll(PDO::FETCH_COLUMN);

        // Fetch images
        $stmtImages = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
        $stmtImages->execute([$project['id']]);
        $project['images'] = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

        // Check if current user has liked this project
        $stmtUserLike = $pdo->prepare("SELECT COUNT(*) FROM project_likes WHERE project_id = ? AND student_id = ?");
        $stmtUserLike->execute([$project['id'], $userId]);
        $project['liked_by_user'] = $stmtUserLike->fetchColumn() > 0;

        // Add to total stars count
        $totalStars += (int)$project['like_count'];
    }

    echo json_encode([
        'success' => true,
        'posts' => $projects,
        'total_projects' => $projectCount,
        'total_stars' => $totalStars
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}