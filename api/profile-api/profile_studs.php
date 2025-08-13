<?php
require_once "../../config/session.php";
require_once "../../config/db.php";
require_once "../../middleware/student_middleware.php";

// Fetch user data including bio and nickname
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, nickname, bio FROM student WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Set defaults if empty
$bio = !empty($user['bio']) ? htmlspecialchars($user['bio']) : "No bio yet...";
$nickname = !empty($user['nickname']) ? htmlspecialchars($user['nickname']) : "No nickname set";
$name = htmlspecialchars($user['name']);
$email = htmlspecialchars($user['email']);
?>

