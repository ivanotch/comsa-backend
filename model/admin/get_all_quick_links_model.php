<?php

declare(strict_types=1);

function get_links(object $pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM quick_links ORDER BY created_at ASC");
    $stmt->execute();
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $links;
}
