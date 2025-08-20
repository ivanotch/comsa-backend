<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once "../../config/session.php";
require_once "../../config/db.php"; // assuming you have DB connection here

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

$eventId = $_POST['editEventId'] ?? null; // you need to send event_id from frontend
$title = $_POST['editEventTitle'] ?? "";
$eventStatus = $_POST['editEventStatus'] ?? "";

$startDateRaw = $_POST['editEventStartDate'] ?? '';
$endDateRaw   = $_POST['editEventEndDate'] ?? '';

$startDateObj = DateTime::createFromFormat('Y-m-d\TH:i', $startDateRaw);
$endDateObj   = DateTime::createFromFormat('Y-m-d\TH:i', $endDateRaw);

$eventStartDate = $startDateObj ? $startDateObj->format('Y-m-d H:i:s') : '';
$eventEndDate   = $endDateObj ? $endDateObj->format('Y-m-d H:i:s') : '';

$featureEvent = filter_var($_POST['editFeatureEvent'] ?? false, FILTER_VALIDATE_BOOLEAN);

try {
    require_once '../../controller/admin/edit_event_contr.php';


    $errors = is_input_empty($eventId, $title, $eventStatus, $eventStartDate, $eventEndDate);

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
    // get current image first
    $stmt = $pdo->prepare("SELECT event_image FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "errors" => "Event not found"
        ]);
        exit;
    }

    $eventImage = $current['event_image']; // default: keep old image

    // check if a new file is uploaded
    if (isset($_FILES['editEventImage']) && $_FILES['editEventImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../uploads/events/";
        $fileTmp = $_FILES['editEventImage']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['editEventImage']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetPath)) {
            $eventImage = "uploads/events/" . $fileName;

            // optionally: delete old image if exists
            if (!empty($current['event_image']) && file_exists("../../" . $current['event_image'])) {
                unlink("../../" . $current['event_image']);
            }
        }
    }

    // update query
    $stmt = $pdo->prepare("UPDATE events 
        SET title = ?, status = ?, start_date = ?, end_date = ?, event_image = ?, carousel_status = ?
        WHERE id = ?");
    $stmt->execute([
        $title,
        $eventStatus,
        $eventStartDate,
        $eventEndDate,
        $eventImage,
        $featureEvent ? 1 : 0,
        $eventId
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Event updated successfully",
        "event_image" => $eventImage
    ]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Database error."]
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
