<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$pageTitle = $pageTitle ?? 'ClubHub';
$currentPage = $currentPage ?? '';
$currentUser = currentUserId() !== null ? findUserById(currentUserId()) : null;
$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('clubhub-theme');
            if (savedTheme === 'dark') {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js" defer></script>
</head>
<body>
    <header>
        <div class="header-brand">
            <h1>ClubHub</h1>
            <button type="button" class="theme-toggle" aria-label="Basculer entre le mode clair et le mode sombre">Mode sombre</button>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"<?= $currentPage === 'index' ? ' aria-current="page"' : '' ?>>Accueil</a></li>
                <li><a href="evenements.php"<?= $currentPage === 'evenements' ? ' aria-current="page"' : '' ?>>Événements</a></li>
                <?php if ($currentUser !== null): ?>
                    <li><a href="mon-compte.php"<?= $currentPage === 'compte' ? ' aria-current="page"' : '' ?>>Mon compte</a></li>
                    <li><a href="deconnexion.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php"<?= $currentPage === 'connexion' ? ' aria-current="page"' : '' ?>>Connexion</a></li>
                    <li><a href="creer-compte.php"<?= $currentPage === 'creer-compte' ? ' aria-current="page"' : '' ?>>Créer un compte</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <?php if ($flashSuccess !== null): ?>
        <p class="form-success" style="max-width:1100px;margin:1rem auto 0;padding:0 1rem;" aria-live="polite"><?= e($flashSuccess) ?></p>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
        <p class="field-error" style="max-width:1100px;margin:1rem auto 0;padding:0 1rem;" aria-live="polite"><?= e($flashError) ?></p>
    <?php endif; ?>
