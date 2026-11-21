<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && isset($_SESSION['registrations'][$id])) {
        unset($_SESSION['registrations'][$id]);
        flash('info', 'Votre inscription a bien été annulée.');
    }
}

redirect('compte.php');
