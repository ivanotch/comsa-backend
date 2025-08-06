<?php

declare(strict_types=1);

function get_like_count(object $pdo, string $projectId)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) AS like_count FROM project_likes WHERE project_id = ?");
    $stmt->execute([$projectId]);
    $likeCount = $stmt->fetchColumn();
    return $likeCount;
}
