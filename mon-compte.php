<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();
$user = findUserById(currentUserId());
if ($user === null) {
    redirect('deconnexion.php');
}

$registrations = registrationsOfUser((int) $user['id']);
$now = new DateTimeImmutable('now');
$upcoming = [];
$past = [];
foreach ($registrations as $reg) {
    $start = toDateTime((string) $reg['starts_at']);
    if ($start !== null && $start < $now) {
        $past[] = $reg;
    } else {
        $upcoming[] = $reg;
    }
}

$pageTitle = 'ClubHub - Mon compte';
$currentPage = 'compte';
require __DIR__ . '/includes/header.php';
?>
    <main>
        <h1>Mon compte</h1>

        <section aria-labelledby="profil">
            <h2 id="profil">Profil</h2>
            <p><strong>Nom :</strong> <?= e((string) $user['name']) ?></p>
            <p><strong>Email :</strong> <?= e((string) $user['email']) ?></p>
            <p><strong>Rôle :</strong> <?= e((string) ($user['role'] ?? '')) ?></p>
            <p><strong>Compte créé le :</strong> <?= e(formatDate((string) $user['created_at'])) ?> (<?= e((string) $user['created_at']) ?>)</p>
        </section>

        <section aria-labelledby="inscriptions-a-venir">
            <h2 id="inscriptions-a-venir">Mes inscriptions — À venir</h2>
            <?php if ($upcoming === []): ?>
                <p>Aucune inscription à venir.</p>
            <?php else: ?>
                <div class="cartes">
                    <?php foreach ($upcoming as $reg): ?>
                        <?php $left = (int) ($reg['places_left'] ?? 0); ?>
                        <article>
                            <p class="club"><?= e((string) $reg['club']) ?></p>
                            <h3><a href="evenement.php?id=<?= e((string) $reg['event_id']) ?>"><?= e((string) $reg['title']) ?></a></h3>
                            <p><time datetime="<?= e(isoDate((string) $reg['starts_at'])) ?>"><?= e(formatTimeRange((string) $reg['starts_at'], (string) ($reg['ends_at'] ?? ''))) ?></time> — <?= e((string) $reg['location']) ?></p>
                            <p><?= e(placesLabel($left)) ?></p>
                            <form method="post" action="annuler-inscription.php" style="margin-top:0.75rem;">
                                <input type="hidden" name="event_id" value="<?= e((string) $reg['event_id']) ?>">
                                <button type="submit">Annuler l'inscription</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section aria-labelledby="inscriptions-passees">
            <h2 id="inscriptions-passees">Mes inscriptions — Passées</h2>
            <?php if ($past === []): ?>
                <p>Aucune inscription passée.</p>
            <?php else: ?>
                <div class="cartes">
                    <?php foreach ($past as $reg): ?>
                        <article>
                            <p class="club"><?= e((string) $reg['club']) ?></p>
                            <h3><a href="evenement.php?id=<?= e((string) $reg['event_id']) ?>"><?= e((string) $reg['title']) ?></a></h3>
                            <p><time datetime="<?= e(isoDate((string) $reg['starts_at'])) ?>"><?= e(formatTimeRange((string) $reg['starts_at'], (string) ($reg['ends_at'] ?? ''))) ?></time> — <?= e((string) $reg['location']) ?></p>
                            <p><em>Événement passé (annulation impossible).</em></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
