<?php


ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $pass = $_POST["password"];
    try {
        require_once '../config/db.php';
        require_once '../model/login_model.php';
        require_once '../controller/login_contr.php';

        $errors = [];

        if (is_input_empty($email, $pass)) {
            $errors["empty_input"] = "Fill in all fields";
        } else {
            $result = get_user($pdo, $email);

            if (!$result) {
                $errors["email"] = "No such user exists";
            } elseif (is_pass_wrong($pass, $result["password"])) {
                $errors["password"] = "Incorrect password!";
            }
        }
        require_once '../config/session.php';

        if ($errors) {
            // Send errors as JSON
            echo json_encode([
                "success" => false,
                "errors" => $errors
            ]);
            exit;
        }

        $newSessionId = session_create_id();
        $sessionId = $newSessionId . "_" . $result["id"];
        session_id($sessionId);

        $_SESSION["user_id"] = $result["id"];
        $_SESSION["user_name"] = htmlspecialchars($result["name"]);
        $_SESSION["user_email"] = htmlspecialchars($result["email"]);
        $_SESSION["user_student_number"] = htmlspecialchars($result["student_number"]);

        $_SESSION["last_regeneration"] = time();

        $pdo = null;
        $stmt = null;

        echo json_encode([
            "success" => true,
            "message" => "login successful"
        ]);
        exit;
    } catch (PDOException $e) {
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
