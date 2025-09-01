<?php

declare(strict_types=1);

function get_all_students(object $pdo) {
    $stmt = $pdo->prepare("
        SELECT id, student_number, name, email, profile_photo, nickname, bio FROM students WHERE role='student' ORDER BY id ASC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $students;
}
