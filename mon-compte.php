<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();
$user = findUserById((int) currentUserId());
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

        <section aria-labelledby="profil" style="margin-bottom:var(--sp-xl);">
            <h2 id="profil">Profil</h2>
            <p><strong>Nom :</strong> <?= e((string) $user['name']) ?></p>
            <p><strong>Email :</strong> <?= e((string) $user['email']) ?></p>
            <p><strong>Rôle :</strong> <?= e((string) ($user['role'] ?? 'student')) ?></p>
            <p><strong>Compte créé le :</strong> <?= e(formatDate((string) $user['created_at'])) ?> (<?= e((string) $user['created_at']) ?>)</p>
        </section>

        <section aria-labelledby="inscriptions-a-venir" style="margin-bottom:var(--sp-xl);">
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
                            <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;">
                                <a href="evenement.php?id=<?= e((string) $reg['event_id']) ?>">Voir le détail</a>
                                <form method="post" action="annuler-inscription.php" style="display:inline;">
                                    <input type="hidden" name="event_id" value="<?= e((string) $reg['event_id']) ?>">
                                    <button type="submit" style="background-color:#dc2626;color:white;border:none;padding:var(--sp-xs) var(--sp-sm);border-radius:var(--radius-sm);cursor:pointer;">Annuler</button>
                                </form>
                            </div>
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
