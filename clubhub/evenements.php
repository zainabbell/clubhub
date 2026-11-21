<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Événements';
$currentPage = 'evenements';
$q = trim($_GET['q'] ?? '');
$clubFilter = trim($_GET['club'] ?? '');
$events = getEvents();
$clubs = [];
foreach ($events as $ev) {
    if (!in_array($ev['club'], $clubs, true)) $clubs[] = $ev['club'];
}
$filtered = [];
foreach ($events as $ev) {
    if ($q !== '' && stripos($ev['title'].' '.$ev['description'], $q) === false && stripos($ev['club'], $q) === false) continue;
    if ($clubFilter !== '' && $ev['club'] !== $clubFilter) continue;
    $filtered[] = $ev;
}
require __DIR__ . '/includes/header.php';
?>
<main>
<h2>Événements</h2>
<form method="get" action="evenements.php">
<input type="text" name="q" value="<?= e($q) ?>" placeholder="Rechercher">
<select name="club">
<option value="">Tous</option>
<?php foreach ($clubs as $c): ?>
<option value="<?= e($c) ?>" <?= $clubFilter===$c?'selected':'' ?>><?= e($c) ?></option>
<?php endforeach; ?>
</select>
<button type="submit">Filtrer</button>
</form>
<section class="events">
<?php foreach ($filtered as $ev): ?>
<article class="card" data-club="<?= e($ev['club']) ?>" data-date="<?= e($ev['date']) ?>" data-places="<?= e((string)$ev['places']) ?>">
<h3><?= e($ev['title']) ?></h3>
<p><?= e($ev['club']) ?> - <?= e($ev['date']) ?> - <?= e($ev['place']) ?></p>
<p><?= e($ev['description']) ?></p>
<a href="evenement.php?id=<?= e((string)$ev['id']) ?>">Voir le détail</a>
</article>
<?php endforeach; ?>
</section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
