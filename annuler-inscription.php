<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mon-compte.php');
}

requireLogin();
$userId = currentUserId();
$eventIdRaw = $_POST['event_id'] ?? '';
$eventId = ctype_digit((string) $eventIdRaw) ? (int) $eventIdRaw : 0;

if ($userId === null || $eventId <= 0) {
    flash('error', "Demande d'annulation invalide.");
    redirect('mon-compte.php');
}

$event = findEvent($eventId);
if ($event === null) {
    flash('error', 'Événement introuvable.');
    redirect('mon-compte.php');
}

$start = toDateTime((string) $event['starts_at']);
if ($start !== null && $start < new DateTimeImmutable('now')) {
    flash('error', "Impossible d'annuler une inscription à un événement passé.");
    redirect('mon-compte.php');
}

$deleted = cancelRegistration($userId, $eventId);
if ($deleted) {
    flash('success', "Votre inscription a été annulée.");
} else {
    flash('error', "Aucune inscription à annuler (ou elle ne vous appartient pas).");
}
redirect('mon-compte.php');
