<?php
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Accueil';
$currentPage = 'index';
$events = array_slice(getEvents(), 0, 2, true);

require __DIR__ . '/includes/header.php';
?>
<main>
    <h1>Bienvenue sur ClubHub</h1>
    <p>La plateforme des clubs étudiants pour découvrir et s'inscrire aux événements du campus.</p>

    <section aria-labelledby="prochains-evenements">
        <h2 id="prochains-evenements">Prochains événements</h2>
        <div class="cartes">
            <?php foreach ($events as $e): ?>
                <article 
                    data-id="<?= e((string)$e['id']) ?>" 
                    data-club="<?= e($e['club']) ?>" 
                    data-date="<?= e($e['date']) ?>" 
                    data-places="<?= e((string)$e['places']) ?>"
                >
                    <div class="card-header-actions">
                        <div>
                            <p class="club"><?= e($e['club']) ?></p>
                            <h3><a href="evenement.php?id=<?= e((string)$e['id']) ?>"><?= e($e['title']) ?></a></h3>
                        </div>
                        <button type="button" class="fav-btn" data-fav-id="<?= e((string)$e['id']) ?>" aria-label="Ajouter aux favoris">★</button>
                    </div>
                    <p><time><?= e($e['date']) ?></time> — <?= e($e['place'] ?? 'Campus') ?></p>
                    <p>Places : <?= e((string)$e['places']) ?></p>
                    <p class="card-footer"><a href="evenement.php?id=<?= e((string)$e['id']) ?>">Voir le détail</a></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
