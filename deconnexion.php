<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logoutUser();
    flash('info', 'Vous êtes déconnecté');
}

redirect('index.php');
