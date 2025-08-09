<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['projectTitle'];
    $projType = $_POST["projectType"];
    $projDesc = $_POST["projectDescription"];

    $projDownloadLink = $_POST["downloadLink"] ?? "";
    $projLiveLink = $_POST["liveLink"] ?? "";
    $projGithubLink = $_POST["githubLink"] ?? "";

    $tags = $_POST['tags'] ?? [];
    $members = $_POST['members'] ?? [];

    try {
        require_once '../config/db.php';
        require_once '../model/upload_proj_model.php';
        require_once '../controller/upload_proj_contr.php';

        $errors = [];

        //required to put title, descriptiom, and type
        if (is_input_empty($title, $projType, $projDesc)) {
            $errors["empty_input"] = "Fill in all fields";
        }

        //if images are not empty, put it in the upload otherwise return an error
        if (empty($_FILES['selectedFiles']['name'][0])) {
            $errors["empty_images"] = "Add at least one image.";
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            foreach ($_FILES['selectedFiles']['tmp_name'] as $i => $tmp) {
                $type = mime_content_type($tmp);
                if (!in_array($type, $allowedTypes)) {
                    $errors["invalid_file_$i"] = "Invalid file type ($type). Allowed: JPG, PNG, GIF.";
                }
            }
        }

        if ($errors) {
            echo json_encode([
                "success" => false,
                "errors" => $errors
            ]);
            exit;
        }

        $uploadedPaths = [];

        foreach ($_FILES['selectedFiles']['name'] as $i => $name) {
            $tmp = $_FILES['selectedFiles']['tmp_name'][$i];

            // Generate safe and unique file name
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $safeName = uniqid("img_", true) . "." . $ext;
            $path = __DIR__ . "/../uploads/projects/" . $safeName; // adjust path if needed
            $relativePath = "uploads/projects//" . $safeName; // path to store in DB

            if (move_uploaded_file($tmp, $path)) {
                $uploadedPaths[] = $relativePath;
            } else {
                $errors["upload_failed_$i"] = "Failed to upload $name.";
            }
        }

        if ($errors) {
            foreach ($uploadedPaths as $uploaded) {
                @unlink(__DIR__ . "/../" . $uploaded);
            }

            echo json_encode([
                "success" => false,
                "errors" => $errors
            ]);
            exit;
        }

        require_once '../config/session.php';

        create_post($pdo, $title, $projType, $projDesc, $projDownloadLink, $projGithubLink, $projLiveLink, $tags, $members, $uploadedPaths);

        echo json_encode([
            "success" => true,
            "message" => "Project uploaded successfully."
        ]);
        
    } catch (PDOException $e) {
        foreach ($uploadedPaths as $uploaded) {
            @unlink(__DIR__ . "/../" . $uploaded);
        }

        echo json_encode([
            "success" => false,
            "errors" => ["server" => "Database error: " . $e->getMessage()]
        ]);
        exit;
    }
} else {
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Invalid request"]
    ]);
    exit;
}
