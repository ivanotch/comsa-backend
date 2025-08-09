<?php

declare(strict_types=1);

function get_comments(object $pdo, string $projectId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT c.comment, c.created_at, s.name, s.profile_photo
            FROM project_comments c
            JOIN students s ON c.student_id = s.id
            WHERE c.project_id = ?
            ORDER BY c.created_at ASC
        ");

        $stmt->execute([$projectId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $comments;
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Failed to fetch comments.']);
    }
}
