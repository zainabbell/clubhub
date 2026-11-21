<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Accueil';
$currentPage = 'index';
$events = array_slice(getEvents(), 0, 2, true);
require __DIR__ . '/includes/header.php';
?>
<main>
<h2>Bienvenue</h2>
<section class="events">
<?php foreach ($events as $e): ?>
<article class="card" data-club="<?= e($e['club']) ?>" data-date="<?= e($e['date']) ?>" data-places="<?= e((string)$e['places']) ?>">
<h3><?= e($e['title']) ?></h3>
<p><?= e($e['club']) ?> - <?= e($e['date']) ?></p>
<a href="evenement.php?id=<?= e((string)$e['id']) ?>">Voir le détail</a>
</article>
<?php endforeach; ?>
</section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
