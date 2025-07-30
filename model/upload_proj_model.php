<?php

declare(strict_types=1);

function create_post(object $pdo, string $title, string $projType, string $projDesc, string $projDownloadLink, string $projGithubLink, string $projLiveLink, array $tags, array $members, array $uploadedPaths)
{

    $studentId = $_SESSION['user_id'];

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("INSERT INTO projects (student_id, project_title, project_description, project_category, download_link, github_link, live_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $studentId,
            $title,
            $projDesc,
            $projType,
            $projDownloadLink ?: null,
            $projGithubLink ?: null,
            $projLiveLink ?: null
        ]);

        $projectId = $pdo->lastInsertId();

        $stmtImg = $pdo->prepare("INSERT INTO project_images (project_id, image_path) VALUES (?, ?)");
        foreach ($uploadedPaths as $path) {
            $stmtImg->execute([$projectId, $path]);
        }

        if (!empty($tags)) {
            $stmtTech = $pdo->prepare("INSERT INTO project_technologies (project_id, technology_name) VALUES (?, ?)");
            foreach ($tags as $tag) {
                $stmtTech->execute([$projectId, $tag]);
            }
        }

        if (!empty($members)) {
            $stmtTeam = $pdo->prepare("INSERT INTO project_team_members (project_id, member_name) VALUES (?, ?)");
            foreach ($members as $member) {
                $stmtTeam->execute([$projectId, $member]);
            }
        }

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
