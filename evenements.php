<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'ClubHub - Tous les événements';
$currentPage = 'evenements';
$q = trim((string) ($_GET['q'] ?? ''));
$events = $q !== '' ? searchEvents($q) : getEvents();
$clubs = getClubs();
$count = count($events);

require __DIR__ . '/includes/header.php';
?>
    <main>
        <h1>Tous les événements</h1>

        <section aria-labelledby="liste-evenements">
            <h2 id="liste-evenements">Liste des événements</h2>

            <form class="filters" role="search" aria-label="Filtrer les événements" method="get" action="evenements.php">
                <div class="filter-group">
                    <label for="search">Rechercher</label>
                    <input type="search" id="search" name="q" placeholder="Ex. Arduino, photo, IA..." autocomplete="off" value="<?= e($q) ?>">
                </div>

                <div class="filter-group">
                    <label for="club-filter">Club</label>
                    <select id="club-filter" name="club">
                        <option value="">Tous les clubs</option>
                        <?php foreach ($clubs as $club): ?>
                            <option value="<?= e($club) ?>"><?= e($club) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort">Trier par</label>
                    <select id="sort" name="sort">
                        <option value="default">Par défaut</option>
                        <option value="date-asc">Date (plus proche)</option>
                        <option value="date-desc">Date (plus lointaine)</option>
                        <option value="places-desc">Places (du plus grand au plus petit)</option>
                        <option value="places-asc">Places (du plus petit au plus grand)</option>
                    </select>
                </div>

                <div class="filter-group filter-group-checkbox">
                    <input type="checkbox" id="fav-filter" name="fav-filter">
                    <label for="fav-filter">Seulement mes favoris</label>
                </div>

                <noscript>
                    <div class="filter-group">
                        <button type="submit">Rechercher</button>
                    </div>
                </noscript>
            </form>

            <p class="results-count" aria-live="polite"><?= e((string) $count) ?> événement<?= $count > 1 ? 's' : '' ?> affiché<?= $count > 1 ? 's' : '' ?></p>

            <p class="empty-message"<?= $count === 0 ? '' : ' hidden' ?>>Aucun événement ne correspond à vos critères de recherche.</p>

            <div class="cartes">
                <?php foreach ($events as $event): ?>
                    <?php $left = (int) ($event['places_left'] ?? $event['capacity'] ?? 0); ?>
                    <article
                        data-id="<?= e((string) $event['id']) ?>"
                        data-club="<?= e((string) $event['club']) ?>"
                        data-date="<?= e((string) $event['starts_at']) ?>"
                        data-places="<?= e((string) $left) ?>"
                    >
                        <div class="card-header-actions">
                            <div>
                                <p class="club"><?= e((string) $event['club']) ?></p>
                                <h3><a href="evenement.php?id=<?= e((string) $event['id']) ?>"><?= e((string) $event['title']) ?></a></h3>
                            </div>
                            <button type="button" class="fav-btn" data-fav-id="<?= e((string) $event['id']) ?>" aria-label="Ajouter aux favoris">★</button>
                        </div>
                        <p><time datetime="<?= e(isoDate((string) $event['starts_at'])) ?>"><?= e(formatTimeRange((string) $event['starts_at'], (string) ($event['ends_at'] ?? ''))) ?></time> — <?= e((string) $event['location']) ?></p>
                        <p><?= e(placesLabel($left)) ?></p>
                        <p class="card-footer"><a href="evenement.php?id=<?= e((string) $event['id']) ?>">Voir le détail</a></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section aria-labelledby="tableau-recap">
            <h2 id="tableau-recap">Récapitulatif</h2>
            <table>
                <thead>
                    <tr>
                        <th scope="col">Événement</th>
                        <th scope="col">Date</th>
                        <th scope="col">Places restantes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <?php $left = (int) ($event['places_left'] ?? $event['capacity'] ?? 0); ?>
                        <tr>
                            <td><?= e((string) $event['title']) ?></td>
                            <td><time datetime="<?= e(isoDate((string) $event['starts_at'])) ?>"><?= e(formatTimeRange((string) $event['starts_at'], (string) ($event['ends_at'] ?? ''))) ?></time></td>
                            <td><?= e(placesLabel($left)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <figure>
            <img src="img.jpg" alt="Illustration d'étudiants participant à divers événements clubs sur le campus">
            <figcaption>Événements ClubHub</figcaption>
        </figure>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
