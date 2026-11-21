<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$id=(int)($_POST['id']??0);
unset($_SESSION['registrations'][$id]);
redirect('compte.php');
