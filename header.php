<?php
require_once __DIR__ . '/bootstrap.php';
if (!isset($pageTitle)) $pageTitle = 'ClubHub';
if (!isset($currentPage)) $currentPage = '';
$flash = takeFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= e($pageTitle) ?> - ClubHub</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
<nav>
<a href="index.php" <?= $currentPage==='index'?'aria-current="page"':'' ?>>Accueil</a>
<a href="evenements.php" <?= $currentPage==='evenements'?'aria-current="page"':'' ?>>Événements</a>
<?php if (currentUser()): ?>
<a href="compte.php">Mon compte</a>
<form method="post" action="deconnexion.php" style="display:inline"><button type="submit" class="link-button">Déconnexion</button></form>
<?php else: ?>
<a href="connexion.php">Connexion</a>
<a href="creer-compte.php">Créer un compte</a>
<?php endif; ?>
</nav>
<h1>ClubHub</h1>
<?php if ($flash): ?>
<div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
</header>
