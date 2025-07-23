<?php

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
        }

        $result = get_user($pdo, $email);

        if (!$result) {
            $errors["no_user"] = "No such user exist";
        }

        if (is_email_wrong($result)) {
            $errors["login_incorrect"] = "Incorrect login info!";
        }

        if (!is_email_wrong($result) && is_pass_wrong($pass, $result["password"])) {
            $errors["login_incorrect"] = "Incorrect login info!";
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

        echo json_encode([
            "success" => true,
            "message" => "Signup successful"
        ]);

        $pdo = null;
        $stmt = null;

        exit;

    } catch (PDOException $e) {
        die("Query Failed: " . $e->getMessage());
    }
} else {
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Invalid request"]
    ]);
    exit;
}
