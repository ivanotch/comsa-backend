<?php
require_once '../config/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Project ID required']);
    exit;
}

$projectId = $_GET['id'];
$userId = $_SESSION['user_id'];

require_once '../config/db.php';

try {
    // Get basic project info
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM projects p
        WHERE p.id = ? AND p.student_id = ?
    ");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    // Get technologies
    $stmtTech = $pdo->prepare("SELECT technology_name FROM project_technologies WHERE project_id = ?");
    $stmtTech->execute([$projectId]);
    $project['technologies'] = $stmtTech->fetchAll(PDO::FETCH_COLUMN);

    // Get team members
    $stmtMembers = $pdo->prepare("SELECT member_name FROM project_team_members WHERE project_id = ?");
    $stmtMembers->execute([$projectId]);
    $project['team_members'] = $stmtMembers->fetchAll(PDO::FETCH_COLUMN);

    // Get images
    $stmtImages = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
    $stmtImages->execute([$projectId]);
    $project['images'] = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'project' => $project
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>