<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

unset($_SESSION['user_id']);
flash('info', 'Vous êtes déconnecté.');
redirect('index.php');
