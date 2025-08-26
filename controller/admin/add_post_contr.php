<?php

declare(strict_types=1);

function is_input_empty(
    string $title,
    string $content,
): array {
    $errors = [];

    // Check title
    if (trim($title) === '') {
        $errors['title'] = 'Event title is required';
    }

    // Check status
    if (trim($content) === '') {
        $errors['content'] = 'Event content is required';
    }

    return $errors;
}