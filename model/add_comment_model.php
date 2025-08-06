<?php

declare(strict_types=1);

function get_student_name(object $pdo, string $studentId) {
    $stmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    return $stmt->fetchColumn();
}