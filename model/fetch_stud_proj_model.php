<?php

declare(strict_types=1);

function get_student_project(object $pdo, string $studentId): array
{
    $query = "
        SELECT 
            p.*,
            s.profile_photo,
            s.name AS student_name,
            (
                SELECT COUNT(*) 
                FROM project_likes 
                WHERE project_id = p.id
            ) AS like_count,
            (
                SELECT COUNT(*) 
                FROM project_comments 
                WHERE project_id = p.id
            ) AS comment_count,
            (
                SELECT COUNT(*) 
                FROM project_likes 
                WHERE project_id = p.id AND student_id = :student_id
            ) AS liked_by_user
        FROM projects p
        JOIN students s ON s.id = p.student_id
        ORDER BY p.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['student_id' => $studentId]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($projects as &$project) {
        // Convert liked_by_user (0/1) → bool
        $project['liked_by_user'] = (bool) $project['liked_by_user'];

        // Convert featured (0/1) → bool
        $project['featured'] = (bool) $project['featured'];

        // visibility stays as string ('public' / 'hidden')
    }

    return $projects;
}
