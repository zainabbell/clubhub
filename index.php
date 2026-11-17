<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'ClubHub - Accueil';
$currentPage = 'index';
$events = getEvents(2);

require __DIR__ . '/includes/header.php';
?>
    <main>
        <h1>Bienvenue sur ClubHub</h1>
        <p>La plateforme des clubs étudiants pour découvrir et s'inscrire aux événements du campus.</p>

        <section aria-labelledby="prochains-evenements">
            <h2 id="prochains-evenements">Prochains événements</h2>
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
                        <p><time datetime="<?= e(isoDate((string) $event['starts_at'])) ?>"><?= e(formatTimeRange((string) $event['starts_at'], (string) ($event['ends_at'] ?? ''))) ?></time></p>
                        <p><?= e(placesLabel($left)) ?></p>
                        <p class="card-footer"><a href="evenement.php?id=<?= e((string) $event['id']) ?>">Voir le détail</a></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
