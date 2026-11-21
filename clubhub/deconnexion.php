<?php
require_once __DIR__ . '/includes/bootstrap.php';
logoutUser();
flash('info','Vous êtes déconnecté');
redirect('index.php');
