<?php

require_once __DIR__ . '../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dsn = $_ENV["DB_DSN"];
$dbusername = $_ENV["DB_USER"];
$dbpassword = $_ENV["DB_PASS"];

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Database connection failed: " . $e->getMessage()]
    ]);
    exit;
}
