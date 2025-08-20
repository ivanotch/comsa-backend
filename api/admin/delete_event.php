<?php
require_once "../../config/session.php";
require_once "../../config/db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Missing event ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT event_image FROM events WHERE id = :id");
    $stmt->execute([":id" => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(["success" => false, "message" => "No event found with that ID"]);
        exit;
    }

    if (!empty($event["event_image"])) {
        $imagePath = "../../" . $event["event_image"]; // assuming stored as "uploads/events/filename.jpg"
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
    $stmt->execute([":id" => $id]);

    echo json_encode(["success" => true, "message" => "Event and image deleted"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
