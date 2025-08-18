<?php

declare(strict_types=1);

function add_quick_links(object $pdo, string $title, string $linkUrl, string $linkCategory, string $icon)
{
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("INSERT INTO quick_links (title, url, category, remix_icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $title,
            $linkUrl,
            $linkCategory,
            $icon ?: null
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("DB error: " . $e->getMessage());
        throw new RuntimeException("Failed to add quick link.");
    }
}
