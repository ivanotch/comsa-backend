<?php
require_once "../../../../backend/config/session.php";
require_once "../../../../backend/config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nickname = trim($_POST['nickname']);

    // Validate nickname (letters only)
    if (!preg_match('/^[A-Za-z]+$/', $nickname)) {
        $_SESSION['error'] = "Nickname can only contain letters (A-Z, a-z)";
        header("Location: ../../../../comsa-now/pages-to-accounts/for-students/profile-studs.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE students SET nickname = ? WHERE id = ?");
        $stmt->execute([$nickname, $user_id]);
        
        $_SESSION['success'] = "Nickname updated successfully!";
        $_SESSION['user_nickname'] = $nickname;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }

    header("Location: ../../../../comsa-now/pages-to-accounts/for-students/profile-studs.php");
    exit;
}

$_SESSION['error'] = "Invalid request";
header("Location: ../../../../comsa-now/pages-to-accounts/for-students/profile-studs.php");
exit;
?>