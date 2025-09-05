<?php
header("Content-Type: application/json");
require_once("../config/db.php");

$response = ["success" => false, "message" => "Something went wrong"];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Collect form data
    $project_id      = $_POST["projectId"] ?? null;
    $title           = $_POST["editProjectTitle"] ?? "";
    $description     = $_POST["editProjectDescription"] ?? "";
    $category        = $_POST["editProjectType"] ?? "";
    $visibility      = $_POST["editVisibility"] ?? "public"; 
    $featured        = isset($_POST["editFeaturedToggle"]) ? 1 : 0; 
    $download_link   = $_POST["editDownload"] ?? null;
    $github_link     = $_POST["editGithub"] ?? null;
    $live_link       = $_POST["editLive"] ?? null;

    $technologies    = $_POST["technologies"] ?? "";
    $existing_images = $_POST["existing_images"] ?? "";

    if (!$project_id) {
        throw new Exception("Missing project ID");
    }

    // Handle techs
    $techArray = array_filter(array_map("trim", explode(",", $technologies)));

    // Handle images
    $imageFolder = "../uploads/projects/";
    if (!is_dir($imageFolder)) {
        mkdir($imageFolder, 0777, true);
    }

    // Start with images the user kept
    $finalImages = [];
    if (!empty($existing_images)) {
        $finalImages = array_filter(array_map("trim", explode(",", $existing_images)));
    }

    // Handle new uploaded files
    if (!empty($_FILES["new_images"])) {
        foreach ($_FILES["new_images"]["tmp_name"] as $key => $tmpName) {
            if ($_FILES["new_images"]["error"][$key] === UPLOAD_ERR_OK) {
                $filename = uniqid() . "_" . basename($_FILES["new_images"]["name"][$key]);
                $targetPath = $imageFolder . $filename;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $finalImages[] = "uploads/projects/" . $filename; // relative path
                }
            }
        }
    }

    // ✅ Update main project
    $stmt = $pdo->prepare("
        UPDATE projects
        SET project_title = ?, 
            project_description = ?, 
            project_category = ?, 
            download_link = ?, 
            github_link = ?, 
            live_link = ?,
            visibility = ?,
            featured = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $title, $description, $category,
        $download_link, $github_link, $live_link, $visibility, $featured, $project_id
    ]);

    // ✅ Update technologies
    $pdo->prepare("DELETE FROM project_technologies WHERE project_id = ?")->execute([$project_id]);
    if (!empty($techArray)) {
        $stmtTech = $pdo->prepare("INSERT INTO project_technologies (project_id, technology_name) VALUES (?, ?)");
        foreach ($techArray as $tech) {
            $stmtTech->execute([$project_id, $tech]);
        }
    }

    // ✅ Handle removed images: delete old files not in $finalImages
    $stmtOld = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
    $stmtOld->execute([$project_id]);
    $oldImages = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

    $removedImages = array_diff($oldImages, $finalImages);
    foreach ($removedImages as $oldImg) {
        $filePath = "../" . $oldImg; // DB stores relative path
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // ✅ Update images in DB
    $pdo->prepare("DELETE FROM project_images WHERE project_id = ?")->execute([$project_id]);
    if (!empty($finalImages)) {
        $stmtImg = $pdo->prepare("INSERT INTO project_images (project_id, image_path) VALUES (?, ?)");
        foreach ($finalImages as $imgPath) {
            $stmtImg->execute([$project_id, $imgPath]);
        }
    }

    $response["success"] = true;
    $response["message"] = "Project updated successfully!";
    $response["images"] = $finalImages;
    $response["technologies"] = $techArray;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
