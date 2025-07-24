<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

session_start();
session_unset();
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logout successful"
]);

exit;


