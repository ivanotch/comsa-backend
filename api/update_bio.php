<?php
require_once "../../config/session.php";
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bio'])) {
    $bio = trim($_POST['bio']);
    $user_id = $_SESSION['user_id'];
    
    // Validate word count
    $wordCount = str_word_count($bio);
    if ($wordCount > 100) {
        $_SESSION['error'] = "Bio must be 100 words or less (current: $wordCount)";
        header("Location: ../../pages-to-accounts/for-students/profile-studs.php");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE students SET bio = ? WHERE id = ?");
        $stmt->execute([$bio, $user_id]);
        
        $_SESSION['success'] = "Bio updated successfully!";
        $_SESSION['user_bio'] = $bio;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    
    header("Location: ../../pages-to-accounts/for-students/profile-studs.php");
    exit;
}

$_SESSION['error'] = "Invalid request";
header("Location: ../../pages-to-accounts/for-students/profile-studs.php");
exit;
?>