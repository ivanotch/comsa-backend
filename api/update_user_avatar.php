<?php
require_once '../config/session.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

try {
    if ($action === 'remove') {
        // Get current photo path from DB
        $stmt = $pdo->prepare("SELECT profile_photo FROM students WHERE id = ?");
        $stmt->execute([$userId]);
        $photo = $stmt->fetchColumn();

        if ($photo) {
            $absolutePath = __DIR__ . '/../uploads/profile/' . basename($photo);
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }

        // Remove from DB
        $update = $pdo->prepare("UPDATE students SET profile_photo = NULL WHERE id = ?");
        $update->execute([$userId]);

        echo json_encode(['success' => true, 'message' => 'Profile photo removed']);
        exit;
    }

    if ($action === 'upload' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($file['type'], $allowed)) {
        throw new Exception("Invalid file type.");
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("File too large.");
    }

    // Get current profile photo before uploading new one
    $stmt = $pdo->prepare("SELECT profile_photo FROM students WHERE id = ?");
    $stmt->execute([$userId]);
    $oldPhoto = $stmt->fetchColumn();

    // Delete old photo if it exists
    if ($oldPhoto) {
        $oldPhotoPath = __DIR__ . '/../uploads/profile/' . basename($oldPhoto);
        if (file_exists($oldPhotoPath)) {
            unlink($oldPhotoPath);
        }
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = 'profile_' . $userId . '_' . time() . '.' . $ext;

    // Folder inside backend/uploads/profile
    $uploadDir = __DIR__ . '/../uploads/profile/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception("Failed to move uploaded file.");
    }

    // Store only relative path in DB
    $relativePath = 'uploads/profile/' . $fileName;

    $stmt = $pdo->prepare("UPDATE students SET profile_photo = ? WHERE id = ?");
    $stmt->execute([$relativePath, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Profile photo updated',
        'path' => $relativePath
    ]);
    exit;
}


    throw new Exception("Invalid request.");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
