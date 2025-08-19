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
    echo json_encode(["success" => false, "message" => "Missing link ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM quick_links WHERE id = :id");
    $stmt->execute([":id" => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Quick link deleted"]);
    } else {
        echo json_encode(["success" => false, "message" => "No link found with that ID"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
