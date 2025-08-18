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

$title = $_POST['linkTitle'] ?? '';
$url = $_POST['linkUrl'] ?? '';
$category = $_POST['linkCategory'] ?? '';
$icon = $_POST['linkIcon'] ?? '';

try {
    require_once '../../config/db.php';
    require_once '../../model/admin/add_quick_links_model.php';
    require_once '../../controller/admin/add_quick_link_contr.php';

    $errors = [];

    if (is_input_empty($title, $url, $category)) {
        $errors['empty_fields'] = "title, url, and category cannot be empty!";
    }

    if (is_link_invalid($url)) {
        $errors['invalid_url'] = "enter a valid url!";
    }

    if ($errors) {
        echo json_encode([
            "success" => false,
            "errors" => $errors
        ]);
        exit;
    }

    add_quick_links($pdo, $title, $url, $category, $icon);

    echo json_encode([
        "success" => true,
        "title" => $title,
        "url" => $url,
        "category" => $category,
        "icon" => $icon
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Database error: " . $e->getMessage()]
    ]);
    exit;
}