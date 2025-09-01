<?php
declare(strict_types=1);

header("Content-Type: application/json");

require_once "../../config/db.php";  
require_once "../../config/session.php"; 

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        exit;
    }

    $id = $_GET["id"] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(["success" => false, "message" => "Invalid student ID"]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id, student_number, name, email, profile_photo, nickname, bio 
        FROM students 
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([":id" => $id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        echo json_encode([
            "success" => true,
            "student" => $student
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Student not found"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
