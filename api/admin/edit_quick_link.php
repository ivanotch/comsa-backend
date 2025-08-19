<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once "../../config/session.php";

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

try {

    $id = $_POST['editLinkId'] ?? "";
    $title = $_POST['editLinkTitle'] ?? "";
    $url = $_POST['editLinkUrl'] ?? "";
    $category = $_POST['editLinkCategory'] ?? "";
    $icon = $_POST['editLinkIcon'] ?? "";

    require_once "../../config/db.php";
    require_once "../../model/admin/edit_quick_link_model.php";
    require_once "../../controller/admin/edit_quick_link_contr.php";

    $errors = [];

    if (empty($id)) {
        $errors['empty_id'] = "No Id Provided.";
    }

    if (is_input_empty($title, $url, $category)) {
        $errors['empty_fields'] = "title, url, and category cannot be empty!";
    }

    if (is_link_invalid($url)) {
        $errors['invalid_url'] = "enter a valid url!";
    }

    if (!is_category_exist($category)) {
        $errors['invalid_category'] = "enter a valid category!";
    }

    if ($errors) {
        echo json_encode([
            "success" => false,
            "errors" => $errors,
            "id" => $id,
            "title" => $title,
            "url" => $url,
            "category" => $category,
            "icon" => $icon,
        ]);
        exit;
    }

    edit_quick_links($pdo, $id, $title, $url, $category, $icon);

    echo json_encode([
        "success" => true,
        "title" => $title,
        "url" => $url,
        "category" => $category,
        "icon" => $icon
    ]);
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
