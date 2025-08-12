<?php
require_once __DIR__ . '/../config/session.php';
// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: /comsa/COMSA-NOW/");
    exit();
}

// Check if user is NOT admin
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'admin') {

    header("Location: /comsa/COMSA-NOW/pages-to-accounts/for-students/student-dashboard.php");
    exit();
}