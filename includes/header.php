<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$pageTitle = $pageTitle ?? 'ClubHub';
$currentPage = $currentPage ?? '';
$currentUser = currentUserId() !== null ? findUserById(currentUserId()) : null;
$flashSuccess = flash('success');
$flashError = flash('error');
$flashInfo = flash('info');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('clubhub-theme');
                if (savedTheme === 'dark') {
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
            <button type="button" class="theme-toggle" aria-label="Basculer entre le mode clair et le mode sombre">Mode sombre</button>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"<?= $currentPage === 'index' ? ' aria-current="page"' : '' ?>>Accueil</a></li>
                <li><a href="evenements.php"<?= $currentPage === 'evenements' ? ' aria-current="page"' : '' ?>>Événements</a></li>
                <?php if ($currentUser !== null): ?>
                    <li><a href="mon-compte.php"<?= $currentPage === 'compte' ? ' aria-current="page"' : '' ?>>Mon compte</a></li>
                    <li>
                        <form method="post" action="deconnexion.php" style="display:inline;">
                            <button type="submit" class="link-button" style="background:none;border:none;color:white;cursor:pointer;font-family:inherit;font-size:inherit;padding:0.25rem 0.5rem;">Déconnexion</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="connexion.php"<?= $currentPage === 'connexion' ? ' aria-current="page"' : '' ?>>Connexion</a></li>
                    <li><a href="creer-compte.php"<?= $currentPage === 'creer-compte' ? ' aria-current="page"' : '' ?>>Créer un compte</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <?php if ($flashSuccess !== null): ?>
        <p class="form-success" style="max-width:1100px;margin:1rem auto 0;padding:0.75rem 1rem;" aria-live="polite"><?= e($flashSuccess) ?></p>
    <?php endif; ?>
    <?php if ($flashError !== null): ?>
        <p class="field-error" style="max-width:1100px;margin:1rem auto 0;padding:0.75rem 1rem;background:#fee2e2;border:1px solid #f87171;border-radius:4px;" aria-live="polite"><?= e($flashError) ?></p>
    <?php endif; ?>
    <?php if ($flashInfo !== null): ?>
        <p style="max-width:1100px;margin:1rem auto 0;padding:0.75rem 1rem;background:#e0f2fe;color:#0369a1;border:1px solid #7dd3fc;border-radius:4px;" aria-live="polite"><?= e($flashInfo) ?></p>
    <?php endif; ?>
