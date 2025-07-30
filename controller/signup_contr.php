<?php

declare(strict_types=1);

function is_input_empty(string $student_number, string $name, string $email, string $pass ) {
    if (empty($student_number) || empty($name) || empty($email) || empty($pass)) {
        return true;
    } else {
        return false;
    }
}

function is_email_invalid(string $email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

function is_email_taken(object $pdo, string $email) {
    if (get_email($pdo, $email)) {
        return true;
    } else {
        return false;
    }
}

function is_student_number_taken(object $pdo, string $student_number) {
    if (get_student_number($pdo, $student_number)) {
        return true;
    } else {
        return false;
    }
}

function create_user(object $pdo, string $student_number, string $name, string $email, string $pass ) {
    set_user( $pdo,  $student_number,  $name,  $email,  $pass);
}