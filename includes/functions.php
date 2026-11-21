<?php
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function getEvents(): array {
    return require __DIR__ . '/../data/events.php';
}
function findEvent(int $id): ?array {
    $events = getEvents();
    return $events[$id] ?? null;
}
