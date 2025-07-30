<?php
declare(strict_types=1);

function is_input_empty(string $title, string $projType, string $projDesc): bool {
    return empty($title) || empty($projDesc) || empty($projType);
}
