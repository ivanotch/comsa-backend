<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once "../../config/session.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "errors" => "Unauthorized access"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "errors" => "Invalid request"
    ]);
    exit;
}

try {
    require_once '../../config/db.php';

    $postId = $_POST['postId'] ?? null;
    $title = trim($_POST['editPostTitle'] ?? "");
    $postContent = trim($_POST['editPostContent'] ?? "");
    $publishStatus = $_POST['editPostStatus'] ?? '';
    $tags = $_POST['tags'] ?? [];

    if (!$postId) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "errors" => ["postId" => "Post ID is required."]
        ]);
        exit;
    }

    if (!is_array($tags)) {
        $tags = [$tags]; // force array
    }

    // Validation
    $errors = [];
    if (empty($title)) $errors["title"] = "Title cannot be empty";
    if (empty($postContent)) $errors["content"] = "Content cannot be empty";

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "errors" => $errors
        ]);
        exit;
    }

    // Fetch current post (to know existing image path)
    $stmt = $pdo->prepare("SELECT post_image FROM admin_post WHERE id = :id");
    $stmt->execute([':id' => $postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "errors" => ["post" => "Post not found."]
        ]);
        exit;
    }

    $currentImage = $post['post_image'];
    $newImagePath = $currentImage; // default keep old

    // Handle file upload
    // Handle file upload
    if (isset($_FILES['editImageUpload']) && $_FILES['editImageUpload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../uploads/adminPosts/";  // ✅ save to adminPosts folder
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tmpName = $_FILES['editImageUpload']['tmp_name'];
        $originalName = basename($_FILES['editImageUpload']['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = uniqid("post_", true) . "." . strtolower($extension);
        $targetPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            // ✅ save relative path to DB
            $newImagePath = "uploads/adminPosts/" . $uniqueName;

            // delete old image if exists and not same
            if ($currentImage && file_exists("../../" . $currentImage)) {
                unlink("../../" . $currentImage);
            }
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "errors" => ["image" => "Failed to upload image."]
            ]);
            exit;
        }
    }


    // Update post
    $stmt = $pdo->prepare("
        UPDATE admin_post 
        SET title = :title, content = :content, post_status = :status, post_image = :image, updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':title' => $title,
        ':content' => $postContent,
        ':status' => $publishStatus,
        ':image' => $newImagePath,
        ':id' => $postId
    ]);

    // Reset and insert tags
    $stmt = $pdo->prepare("DELETE FROM admin_tags WHERE post_id = :id");
    $stmt->execute([':id' => $postId]);

    if (!empty($tags)) {
        $stmt = $pdo->prepare("INSERT INTO admin_tags (post_id, tag_name) VALUES (:post_id, :tag)");
        foreach ($tags as $tag) {
            $stmt->execute([
                ':post_id' => $postId,
                ':tag' => trim($tag)
            ]);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Post updated successfully",
        "post" => [
            "id" => $postId,
            "title" => $title,
            "content" => $postContent,
            "status" => $publishStatus,
            "image" => $newImagePath,
            "tags" => $tags
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Database error: " . $e->getMessage()]
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Unexpected server error."]
    ]);
    exit;
}
