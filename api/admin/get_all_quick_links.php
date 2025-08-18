<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once '../../config/session.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "errors" => "Unauthorized access"
    ]);
    exit;
}

try {

    require_once '../../config/db.php';
    require_once '../../model/admin/get_all_quick_links_model.php';

    $links = get_links($pdo);

    echo json_encode([
        "success" => true,
        "links" => $links
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
