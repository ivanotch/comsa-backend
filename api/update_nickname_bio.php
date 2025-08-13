<?php
require_once "../config/session.php";
require_once "../config/db.php";

$bio = "No bio yet...";
$nickname = 'No nickname set';
$name = '';
$email = '';

// Fetch user data including bio and nickname
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT student_number, name, email, nickname, bio FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

$bio = !empty($user['bio']) ? htmlspecialchars($user['bio']) : $bio;
$nickname = !empty($user['nickname']) ? htmlspecialchars($user['nickname']) : $nickname;
$name = htmlspecialchars($user['name']);
$email = htmlspecialchars($user['email']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nickname'])) {
    $new_nickname = trim($_POST['nickname']);

    // Validate nickname (letters only)
    if (!preg_match('/^[A-Za-z]+$/', $new_nickname)) {
        $_SESSION['error'] = "Nickname can only contain letters (A-Z, a-z)";
        header("Location: ../../../comsa-now/pages-to-accounts/for-students/profile-studs.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE students SET nickname = ? WHERE id = ?");
        $stmt->execute([$new_nickname, $user_id]);

        $_SESSION['success'] = "Nickname updated successfully!";
        $_SESSION['user_nickname'] = $new_nickname;
        $nickname = $new_nickname; 

        header("Location: profile-studs.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../../../comsa-now/pages-to-accounts/for-students/profile-studs.php");
        exit;
    }
}
?>