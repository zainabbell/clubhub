<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) currentUserId();
    $eventId = (int) ($_POST['event_id'] ?? $_POST['id'] ?? 0);

    if ($eventId > 0) {
        $cancelled = cancelRegistration($userId, $eventId);
        if ($cancelled) {
            flash('info', 'Votre inscription a bien été annulée.');
        } else {
            flash('error', "Impossible d'annuler cette inscription (événement passé ou non trouvé).");
        }
    }
}

redirect('mon-compte.php');
