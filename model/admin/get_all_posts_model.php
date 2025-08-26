<?php

declare(strict_types=1);

function get_all_posts(object $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.title,
            p.post_image,
            p.content,
            p.post_status,
            p.created_at,
            p.updated_at,
            a.id AS admin_id,
            a.name AS admin_username,
            a.email AS admin_email,
            a.profile_photo AS admin_photo,
            a.nickname AS admin_nickname,
            COUNT(DISTINCT pl.id) AS like_count,
            COUNT(DISTINCT pc.id) AS comment_count,
            GROUP_CONCAT(DISTINCT t.tag_name) AS tags
        FROM admin_post p
        LEFT JOIN students a ON p.admin_id = a.id
        LEFT JOIN post_likes pl ON p.id = pl.post_id
        LEFT JOIN post_comments pc ON p.id = pc.post_id
        LEFT JOIN admin_tags t ON p.id = t.post_id
        GROUP BY p.id, a.id, a.name, a.email, a.profile_photo, a.nickname
        ORDER BY p.created_at ASC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert tags string -> array
    foreach ($posts as &$post) {
        $post['tags'] = $post['tags'] ? explode(',', $post['tags']) : [];

        // Fetch comments for this post
        $cstmt = $pdo->prepare("
            SELECT 
                pc.id,
                pc.comment,
                pc.created_at,
                s.id AS student_id,
                s.name AS student_name,
                s.profile_photo AS student_photo,
                s.nickname AS student_nickname
            FROM post_comments pc
            JOIN students s ON pc.student_id = s.id
            WHERE pc.post_id = ?
            ORDER BY pc.created_at ASC
        ");
        $cstmt->execute([$post['id']]);
        $post['comments'] = $cstmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $posts;
}
