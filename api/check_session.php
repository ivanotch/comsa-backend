<?php

header('Content-Type: application/json');

require_once "../config/session.php";

echo json_encode([
    "loggedIn" => isset($_SESSION['user_id'])
]);