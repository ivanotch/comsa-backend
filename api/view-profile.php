<?php
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/config/db.php';

// Check if profile ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../pages-to-accounts/for-students/profile-studs.php");
    exit;
}

$profile_id = $_GET['id'];

// Get profile data
try {
    $stmt = $pdo->prepare("
        SELECT 
            s.id, 
            s.name as username, 
            s.email,
            s.profile_photo as profile_picture,
            s.nickname,
            s.bio,
            s.created_at,
            COUNT(DISTINCT p.id) as total_projects,
            COALESCE(COUNT(DISTINCT pl.id), 0) as total_stars
        FROM students s
        LEFT JOIN projects p ON s.id = p.student_id
        LEFT JOIN project_likes pl ON p.id = pl.project_id
        WHERE s.id = ?
        GROUP BY s.id, s.name, s.email, s.profile_photo, s.nickname, s.bio, s.created_at
    ");
    
    $stmt->execute([$profile_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        header("Location: ../../comsa/pages-to-accounts/for-students/profile-studs.php");
        exit;
    }
    
    // Get user's projects
    $projects_stmt = $pdo->prepare("
        SELECT p.*, 
               GROUP_CONCAT(DISTINCT pt.technology_name) as technologies,
               GROUP_CONCAT(DISTINCT tm.member_name) as team_members,
               (SELECT GROUP_CONCAT(image_path) FROM project_images WHERE project_id = p.id) as images,
               COUNT(DISTINCT pl.id) as like_count
        FROM projects p
        LEFT JOIN project_technologies pt ON p.id = pt.project_id
        LEFT JOIN project_team_members tm ON p.id = tm.project_id
        LEFT JOIN project_likes pl ON p.id = pl.project_id
        WHERE p.student_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    
    $projects_stmt->execute([$profile_id]);
    $projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

