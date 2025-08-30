<?php

declare(strict_types=1);

function is_input_empty(
    string $fn,
    string $ln,
    string $email,
    string $studentId
): array {
    $errors = [];

    // Check name
    if (trim($fn) === '') {
        $errors['first_name'] = 'Student first name is required';
    }

    // Check status
    if (trim($ln) === '') {
        $errors['last_name'] = 'Student last name is required';
    }

    if (trim($email) === '') {
        $errors['email'] = 'Student email is required';
    }

    if (trim($studentId) === '') {
        $errors['id'] = 'Student id is required';
    }
    return $errors;
}
