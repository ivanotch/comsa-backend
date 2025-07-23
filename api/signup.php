<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_number = $_POST["student_number"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $pass = $_POST["password"];

    try {
        require_once '../config/db.php';
        require_once '../model/signup_model.php';
        require_once '../controller/signup_contr.php';

        $errors = [];

        if (is_input_empty($student_number, $name, $email, $pass)) {
            $errors["empty"] = "Fill in all fields!";
        }
        if (is_email_invalid($email)) {
            $errors["invalid_email"] = "Invalid email used!";
        }
        if (is_email_taken($pdo, $email)) {
            $errors["email_taken"] = "Email is already in use!";
        }
        if (is_student_number_taken($pdo, $student_number)) {
            $errors["student_number_taken"] = "student number already registered!";
        }

        //final error handler if 
        if ($errors) {
            // Send errors as JSON
            echo json_encode([
                "success" => false,
                "errors" => $errors
            ]);
            exit;
        }

        create_user($pdo, $student_number, $name, $email, $password);

        $pdo = null;
        $stmt = null;

        // return a success status on javascript to handle changes in the fututre
        echo json_encode([
            "success" => true,
            "message" => "Signup successful"
        ]);
        exit;

    } catch (PDOException $e) {

        echo json_encode([
            "success" => false,
            "errors" => ["server" => "Query Failed: " . $e->getMessage()]
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
