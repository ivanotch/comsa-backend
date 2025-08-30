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
    // Verify project belongs to user
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND student_id = ?");
    $stmt->execute([$projectId, $userId]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not authorized to update this project']);
        exit;
    }

    // Get form data
    $title = $_POST['project_title'] ?? '';
    $category = $_POST['project_category'] ?? '';
    $description = $_POST['project_description'] ?? '';
    $downloadLink = $_POST['download_link'] ?? null;
    $liveLink = $_POST['live_link'] ?? null;
    $githubLink = $_POST['github_link'] ?? null;
    $technologies = $_POST['technologies'] ?? [];
    $teamMembers = $_POST['team_members'] ?? [];

    // Validate required fields
    if (empty($title) || empty($category) || empty($description)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title, category, and description are required']);
        exit;
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Update project
    $stmt = $pdo->prepare("
        UPDATE projects SET
            project_title = ?,
            project_category = ?,
            project_description = ?,
            download_link = ?,
            live_link = ?,
            github_link = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $title, $category, $description,
        $downloadLink, $liveLink, $githubLink,
        $projectId
    ]);

    // Update technologies
    $stmt = $pdo->prepare("DELETE FROM project_technologies WHERE project_id = ?");
    $stmt->execute([$projectId]);
    
    $stmt = $pdo->prepare("INSERT INTO project_technologies (project_id, technology_name) VALUES (?, ?)");
    foreach ($technologies as $tech) {
        if (!empty($tech)) {
            $stmt->execute([$projectId, $tech]);
        }
    }

    // Update team members
    $stmt = $pdo->prepare("DELETE FROM project_team_members WHERE project_id = ?");
    $stmt->execute([$projectId]);
    
    $stmt = $pdo->prepare("INSERT INTO project_team_members (project_id, member_name) VALUES (?, ?)");
    foreach ($teamMembers as $member) {
        if (!empty($member)) {
            $stmt->execute([$projectId, $member]);
        }
    }

    // Handle file uploads if any
    if (!empty($_FILES['mediaFiles']['name'][0])) {
        $uploadDir = '../../uploads/projects/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $stmt = $pdo->prepare("INSERT INTO project_images (project_id, image_path) VALUES (?, ?)");
        
        foreach ($_FILES['mediaFiles']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['mediaFiles']['error'][$key] === UPLOAD_ERR_OK) {
                // Generate safe and unique file name (consistent with upload code)
                $name = $_FILES['mediaFiles']['name'][$key];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $safeName = uniqid("img_", true) . "." . $ext;
                $targetPath = $uploadDir . $safeName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Use the same path format as the upload code
                    $stmt->execute([$projectId, 'uploads/projects/' . $safeName]);
                }
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Project updated successfully'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>