<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Événements';
$currentPage = 'evenements';

$q = trim($_GET['q'] ?? '');
$clubFilter = trim($_GET['club'] ?? '');
$events = getEvents();

// Liste unique des clubs pour le select (Défi S04)
$clubs = [];
foreach ($events as $ev) {
    if (!in_array($ev['club'], $clubs, true)) {
        $clubs[] = $ev['club'];
    }
}

// Filtrage côté serveur (Défi S04 / sans JavaScript)
$filtered = [];
foreach ($events as $ev) {
    if ($q !== '' && mb_stripos($ev['title'] . ' ' . ($ev['description'] ?? ''), $q) === false && mb_stripos($ev['club'], $q) === false) {
        continue;
    }
    if ($clubFilter !== '' && $ev['club'] !== $clubFilter) {
        continue;
    }
    $filtered[] = $ev;
}

require __DIR__ . '/includes/header.php';
?>
<main>
    <h1>Tous les événements</h1>

    <!-- Formulaire de filtres (compatible JS TP2 et fallback GET serveur S04) -->
    <form method="get" action="evenements.php" class="filters" role="search">
        <div class="filter-group">
            <label for="search">Recherche</label>
            <input type="search" id="search" name="q" value="<?= e($q) ?>" placeholder="Ex: Arduino, Photo, IA..." autocomplete="off">
        </div>

        <div class="filter-group">
            <label for="club-filter">Club</label>
            <select id="club-filter" name="club">
                <option value="">Tous les clubs</option>
                <?php foreach ($clubs as $c): ?>
                    <option value="<?= e($c) ?>" <?= $clubFilter === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="sort">Trier par</label>
            <select id="sort" name="sort">
                <option value="default">Ordre par défaut</option>
                <option value="date-asc">Date la plus proche</option>
                <option value="date-desc">Date la plus lointaine</option>
                <option value="places-desc">Places : du plus grand au plus petit</option>
                <option value="places-asc">Places : du plus petit au plus grand</option>
            </select>
        </div>

        <div class="filter-group filter-group-checkbox">
            <input type="checkbox" id="fav-filter" name="fav">
            <label for="fav-filter">Seulement mes favoris</label>
        </div>

        <button type="submit" style="display:none">Filtrer</button>
    </form>

    <p class="results-count" aria-live="polite"><?= count($filtered) ?> événement<?= count($filtered) > 1 ? 's' : '' ?> affiché<?= count($filtered) > 1 ? 's' : '' ?></p>
    <p class="empty-message" <?= count($filtered) === 0 ? '' : 'hidden' ?> aria-live="polite">Aucun événement ne correspond à vos critères de recherche.</p>

    <section aria-labelledby="liste-evenements">
        <h2 id="liste-evenements">Liste des événements</h2>
        <div class="cartes">
            <?php foreach ($filtered as $ev): ?>
                <article 
                    data-id="<?= e((string)$ev['id']) ?>" 
                    data-club="<?= e($ev['club']) ?>" 
                    data-date="<?= e($ev['date']) ?>" 
                    data-places="<?= e((string)$ev['places']) ?>"
                >
                    <div class="card-header-actions">
                        <div>
                            <p class="club"><?= e($ev['club']) ?></p>
                            <h3><a href="evenement.php?id=<?= e((string)$ev['id']) ?>"><?= e($ev['title']) ?></a></h3>
                        </div>
                        <button type="button" class="fav-btn" data-fav-id="<?= e((string)$ev['id']) ?>" aria-label="Ajouter aux favoris">★</button>
                    </div>
                    <p><time><?= e($ev['date']) ?></time> — <?= e($ev['place'] ?? 'Campus') ?></p>
                    <p>Places : <?= e((string)$ev['places']) ?></p>
                    <p class="card-footer"><a href="evenement.php?id=<?= e((string)$ev['id']) ?>">Voir le détail</a></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
