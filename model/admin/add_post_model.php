<?php

declare(strict_types=1);

function add_post(object $pdo, string $id, string $title, ?string $postImage, string $postContent, string $publishOption, array $tags): int
{
    $stmt = $pdo->prepare("INSERT INTO admin_post (admin_id, title, post_image, content, post_status)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $id,
        $title,
        $postImage ?? '',
        $postContent,
        $publishOption
    ]);

    $postId = (int) $pdo->lastInsertId();

    if (!empty($tags)) {
        $stmtTag = $pdo->prepare("INSERT INTO admin_tags (post_id, tag_name) VALUES (?, ?)");
        foreach ($tags as $tag) {
            $stmtTag->execute([$postId, $tag]);
        }
    }

    return $postId;
}


function update_post_image(object $pdo, int $postId, string $imagePath): void
{
    $stmt = $pdo->prepare("UPDATE admin_post SET post_image = ? WHERE id = ?");
    $stmt->execute([$imagePath, $postId]);
}
