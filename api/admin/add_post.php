<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once '../../config/session.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "errors" => ["general" => "Unauthorized Request"]
    ]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "errors" => ["general" => "Unauthorized Access"]
    ]);
    exit;
}

$title = $_POST['postTitle'] ?? "";
$postContent = $_POST['postContent'] ?? "";
$publishOption = $_POST['publishOption'] ?? '';
$tags = $_POST['tags'] ?? [];

try {
    require_once '../../config/db.php';
    require_once '../../model/admin/add_post_model.php';
    require_once '../../controller/admin/add_post_contr.php';

    $errors = is_input_empty($title, $postContent);

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "errors" => $errors
        ]);
        exit;
    }

    // Start transaction here
    $pdo->beginTransaction();

    $postId = add_post($pdo, $title, null, $postContent, $publishOption, $tags);

    // Handle image upload
    if (!empty($_FILES['imageUpload']['name'])) {
        $fileTmpPath = $_FILES['imageUpload']['tmp_name'];
        $fileName = basename($_FILES['imageUpload']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExtension, $allowedExts)) {
            $pdo->rollBack();
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "errors" => ["image" => "Invalid file type. Allowed: jpg, jpeg, png, gif, webp"]
            ]);
            exit;
        }

        // Optional: file size check (max 5MB)
        if ($_FILES['imageUpload']['size'] > 5 * 1024 * 1024) {
            $pdo->rollBack();
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "errors" => ["image" => "File size exceeds 5MB limit."]
            ]);
            exit;
        }

        $safeFileName = uniqid("img_", true) . "." . $fileExtension;
        $uploadDir = __DIR__ . "/../../uploads/adminPosts/";
        $destPath = $uploadDir . $safeFileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $postPath = "uploads/adminPosts/" . $safeFileName;
            update_post_image($pdo, $postId, $postPath);
        } else {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "errors" => ["image" => "Failed to upload file."]
            ]);
            exit;
        }
    }

    // Commit after both DB + image succeed
    $pdo->commit();

    echo json_encode([
        "success" => true,
        "title" => $title,
        "content" => $postContent,
        "option" => $publishOption,
        "tags" => $tags,
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("DB error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Internal server error. Please try again later."]
    ]);
    exit;
}
