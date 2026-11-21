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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - ClubHub</title>
    <script>
      (function() {
        try {
          var theme = localStorage.getItem('clubhub-theme');
          if (theme === 'dark') {
            document.documentElement.dataset.theme = 'dark';
          }
        } catch (e) {}
      })();
    </script>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js" defer></script>
</head>
<body>
<header>
    <div class="header-brand">
        <h1>ClubHub</h1>
        <button type="button" class="theme-toggle" aria-label="Changer de thème">Mode sombre</button>
    </div>
    <nav>
        <ul>
            <li><a href="index.php" <?= $currentPage === 'index' ? 'aria-current="page"' : '' ?>>Accueil</a></li>
            <li><a href="evenements.php" <?= $currentPage === 'evenements' ? 'aria-current="page"' : '' ?>>Événements</a></li>
            <?php if (currentUser()): ?>
                <li><a href="compte.php" <?= $currentPage === 'compte' ? 'aria-current="page"' : '' ?>>Mon compte</a></li>
                <li>
                    <form method="post" action="deconnexion.php" style="display:inline">
                        <button type="submit" class="link-button" style="background:none;border:none;color:white;cursor:pointer;font-family:inherit;font-size:inherit;padding:0.25rem 0.5rem;">Déconnexion</button>
                    </form>
                </li>
            <?php else: ?>
                <li><a href="connexion.php" <?= $currentPage === 'connexion' ? 'aria-current="page"' : '' ?>>Connexion</a></li>
                <li><a href="creer-compte.php" <?= $currentPage === 'creer-compte' ? 'aria-current="page"' : '' ?>>Créer un compte</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php if ($flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>
</header>
