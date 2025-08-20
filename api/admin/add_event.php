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

$title = $_POST['eventTitle'] ?? "";
$eventStatus = $_POST['eventStatus'] ?? "";

$startDateRaw = $_POST['eventStartDate'] ?? '';
$endDateRaw   = $_POST['eventEndDate'] ?? '';

$startDateObj = DateTime::createFromFormat('Y-m-d\TH:i', $startDateRaw);
$endDateObj   = DateTime::createFromFormat('Y-m-d\TH:i', $endDateRaw);

$eventStartDate = $startDateObj ? $startDateObj->format('Y-m-d H:i:s') : '';
$eventEndDate   = $endDateObj ? $endDateObj->format('Y-m-d H:i:s') : '';

$featureEvent = filter_var($_POST['featureEvent'] ?? false, FILTER_VALIDATE_BOOLEAN);

try {
    require_once '../../config/db.php';
    require_once '../../model/admin/add_event_model.php';
    require_once '../../controller/admin/add_event_contr.php';

    $errors = is_input_empty($title, $eventStatus, $eventStartDate, $eventEndDate);

    if (empty($errors)) {
        $dateErrors = check_date($eventStartDate, $eventEndDate);
        $errors = array_merge($errors, $dateErrors);
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "errors" => $errors
        ]);
        exit;
    }

    // Step 1: Insert event without image path
    $eventId = add_event(
        $pdo,
        $title,
        $eventStatus,
        $eventStartDate,
        $eventEndDate,
        null, // no image yet
        (bool)$featureEvent
    );

    // Step 2: Upload image if provided
    if (!empty($_FILES['eventImage']['name'])) {
        $fileTmpPath = $_FILES['eventImage']['tmp_name'];
        $fileName = basename($_FILES['eventImage']['name']);
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
        $uploadDir = __DIR__ . "/../../uploads/events/";
        $destPath = $uploadDir . $safeFileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $eventImagePath = "uploads/events/" . $safeFileName;
            // Step 3: Update event with image path
            update_event_image($pdo, $eventId, $eventImagePath);
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
    
} catch (PDOException $e) {
    error_log("DB error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Internal server error. Please try again later."]
    ]);
    exit;
}
