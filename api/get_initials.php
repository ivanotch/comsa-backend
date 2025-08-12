<?php

require_once '../config/session.php';

header("Content-Type: application/json");

echo json_encode([
    'name' => $_SESSION['user_name'] ?? null
]);