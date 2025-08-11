<?php

declare(strict_types=1);

function get_user_avatar(object $pdo, string $studentId) {
    $stmt = $pdo->prepare("SELECT profile_photo FROM students WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $studentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row;
}