<?php

declare(strict_types=1);

function is_input_empty(string $title, string $linkUrl, string $linkCategory): bool
{
    return empty($title) || empty($linkUrl) || empty($linkCategory);
}

function is_link_invalid(string $url)
{
    return !filter_var($url, FILTER_VALIDATE_URL);
}
