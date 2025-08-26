<?php

declare(strict_types=1);

function get_all_posts(object $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM admin_post ORDER BY created_at ASC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $posts;

}