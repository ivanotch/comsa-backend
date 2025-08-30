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
        "errors" => "Unauthorized Request",
    ]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        'errors' => 'Unauthorized Access'
    ]);
    exit;
}

try {
    $fn = $_POST["studentFirstName"] ?? "";
    $ln = $_POST["studentLastName"] ?? "";
    $email = $_POST["studentEmail"] ?? "";
    $studentId = $_POST["studentID"] ?? "";

    require_once '../../config/db.php';
    require_once '../../model/admin/add_student_model.php';
    require_once '../../controller/admin/add_student_contr.php';

    $errors = is_input_empty($fn, $ln, $email, $studentId);

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "errors" => $errors
        ]);
        exit;
    }

    $name = "$fn $ln";
    $password = strtoupper($ln);

    $studentDataId = add_student($pdo, $studentId, $name, $email, $password, null);

    if (!empty($_FILES['studentAvatar']['name'])) {
        $fileTmpPath = $_FILES['studentAvatar']['tmp_name'];
        $fileName = basename($_FILES['studentAvatar']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExtension, $allowedExts)) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "errors" => ["image" => "Invalid file type. Allowed: jpg, jpeg, png, gif, webp"]
            ]);
            exit;
        }

        $safeFileName = uniqid("img_", true) . "." . $fileExtension;
        $uploadDir = __DIR__ . "/../../uploads/profile/";
        $destPath = $uploadDir . $safeFileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $studentImagePath = "uploads/profile/" . $safeFileName;
            // Step 3: Update event with image path
            update_student_profile($pdo, (int)$studentDataId, $studentImagePath);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "errors" => ["image" => "Failed to upload file."]
            ]);
            exit;
        }
    }

    echo json_encode([
        "success" => true
    ]);
} catch (Exception $e) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "errors" => ["general" => $e->getMessage()]
    ]);
    exit;
    
} catch (PDOException $e) {
    error_log("DB error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Internal server error. Please try again later."]
    ]);
    exit;
}
