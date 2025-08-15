<?php
require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../middleware/student_middleware.php";


//unused php code


// Fetch user data including bio and nickname
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, nickname, bio FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Set defaults if empty
$bio = !empty($user['bio']) ? htmlspecialchars($user['bio']) : "No bio yet...";
$nickname = !empty($user['nickname']) ? htmlspecialchars($user['nickname']) : "No nickname set";
$name = htmlspecialchars($user['name']);
$email = htmlspecialchars($user['email']);
?>

