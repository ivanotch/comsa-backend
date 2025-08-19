<?php

declare(strict_types=1);

function edit_quick_links(object $pdo, string $id, string $title, string $url, string $category, string $icon) {
    $stmt = $pdo->prepare("UPDATE quick_links SET title= :title, url= :url, category= :category, remix_icon= :icon WHERE id= :id");

    return $stmt->execute([
        ':title' => $title,
        ':url' => $url,
        ':category' => $category,
        ':icon' => $icon,
        ':id' => $id
    ]);
}