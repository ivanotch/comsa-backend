<?php

declare(strict_types=1);

function is_input_empty(
    string $id,
    string $title,
    string $status,
    string $startDate,
    string $endDate,
): array {
    $errors = [];

    // Check title
    if (trim($title) === '') {
        $errors['title'] = 'Event title is required';
    }

    if (trim($title) === '') {
        $errors['id'] = 'Event id is required';
    }

    // Check status
    if (trim($status) === '') {
        $errors['status'] = 'Event status is required';
    }

    // Check start date
    if (trim($startDate) === '') {
        $errors['startDate'] = 'Start date is required';
    } elseif (!DateTime::createFromFormat('Y-m-d H:i:s', $startDate)) {
        $errors['startDate'] = 'Invalid start date format (expected Y-m-d H:i:s)';
    }

    // Check end date
    if (trim($endDate) === '') {
        $errors['endDate'] = 'End date is required';
    } elseif (!DateTime::createFromFormat('Y-m-d H:i:s', $endDate)) {
        $errors['endDate'] = 'Invalid end date format (expected Y-m-d H:i:s)';
    }

    return $errors;
}

function check_date(string $startDate, string $endDate): array
{
    $errors = [];

    $start = DateTime::createFromFormat('Y-m-d H:i:s', $startDate);
    $end = DateTime::createFromFormat('Y-m-d H:i:s', $endDate);

    if ($start && $end && $end < $start) {
        $errors["date"] = "End date cannot be earlier than start date.";
    }

    return $errors;
}
