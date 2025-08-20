<?php

declare(strict_types=1);

function add_event(object $pdo, string $title, string $status, string $startDate, string $endDate, ?string $eventImage, bool $featureEvent)
{
    $stmt = $pdo->prepare("INSERT INTO events (title, status, start_date, end_date, event_image, carousel_status)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $title,
        $status,
        $startDate,
        $endDate,
        $eventImage,
        $featureEvent
    ]);

    return $pdo->lastInsertId();
}

function update_event_image(object $pdo, int $eventId, string $imagePath)
{
    $stmt = $pdo->prepare("UPDATE events SET event_image = ? WHERE id = ?");
    $stmt->execute([$imagePath, $eventId]);
}
