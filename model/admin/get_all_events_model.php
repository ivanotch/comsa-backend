<?php

declare(strict_types=1);

function get_all_events(object $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM events ORDER BY start_date ASC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $events;

}