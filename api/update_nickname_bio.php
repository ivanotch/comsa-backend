<?php
require_once "/../backend/config/session.php";
require_once "/../backend/config/db.php";
require_once '/../backend/middleware/student_middleware.php';

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

// Handle bio update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bio'])) {
    $new_bio = trim($_POST['bio']);

    // Validate bio length (100 characters max)
    if (strlen($new_bio) > 100) {
        $_SESSION['error'] = "Bio must be 100 characters or less";
        header("Location: profile-studs.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE students SET bio = ? WHERE id = ?");
        $stmt->execute([$new_bio, $user_id]);

        $_SESSION['success'] = "Bio updated successfully!";
        $bio = $new_bio;

        header("Location: profile-studs.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: profile-studs.php");
        exit;
    }
}

// Handle nickname update (your existing code)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nickname'])) {
    $new_nickname = trim($_POST['nickname']);

    // Validate nickname (letters only)
    if (!preg_match('/^[A-Za-z]+$/', $new_nickname)) {
        $_SESSION['error'] = "Nickname can only contain letters (A-Z, a-z)";
        header("Location: profile-studs.php");
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
        header("Location: profile-studs.php");
        exit;
    }
}
?>