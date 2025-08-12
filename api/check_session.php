<?php

require_once "../config/session.php";

header('Content-Type: application/json');

$role = $_SESSION['user_role'] ?? null;

echo json_encode([
    "loggedIn" => isset($_SESSION['user_id']),
    "role" => $role
]);