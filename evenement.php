<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$idRaw = $_GET['id'] ?? null;
$event = null;
if ($idRaw !== null && ctype_digit((string) $idRaw)) {
    $event = findEvent((int) $idRaw);
}

if ($event === null) {
    http_response_code(404);
    $pageTitle = 'ClubHub - Événement introuvable';
    $currentPage = 'evenement';
    require __DIR__ . '/includes/header.php';
    ?>
    <main>
        <h1>Événement introuvable</h1>
        <p>L'événement demandé est absent, inconnu, ou son identifiant est invalide.</p>
        <p><a href="evenements.php">Retour à la liste des événements</a></p>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = 'ClubHub - ' . (string) $event['title'];
$currentPage = 'evenement';
$eventId = (int) $event['id'];
$left = (int) ($event['places_left'] ?? 0);
$startDt = toDateTime((string) $event['starts_at']);
$isPast = $startDt !== null && $startDt < new DateTimeImmutable('now');
$userId = currentUserId();
$already = ($userId !== null) ? isRegistered($userId, $eventId) : false;

$errors = [];
$motivation = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $userId = currentUserId();
    $motivation = trim((string) ($_POST['motivation'] ?? ''));
    $reglement = isset($_POST['reglement']) ? 'accepte' : '';

    if ($isPast) {
        $errors['global'] = "Impossible de s'inscrire à un événement passé.";
    }
    if ($left <= 0) {
        $errors['global'] = "Cet événement est complet, aucune inscription n'est possible.";
    }
    if (mb_strlen($motivation) > 300) {
        $errors['motivation'] = 'La motivation est limitée à 300 caractères.';
    }
    if ($reglement !== 'accepte') {
        $errors['reglement'] = 'Vous devez accepter le règlement du club.';
    }

    if ($errors === [] && $userId !== null) {
        $result = registerUser($userId, $eventId, $motivation);
        if ($result === 'ok') {
            flash('success', 'Inscription confirmée pour « ' . (string) $event['title'] . ' ».');
            redirect('evenement.php?id=' . $eventId);
        }
        if ($result === 'already') {
            $errors['global'] = 'Vous êtes déjà inscrit à cet événement.';
        } elseif ($result === 'full') {
            $errors['global'] = 'Désolé, cet événement est complet.';
        }
        // Rafraîchir les données après la tentative
        $event = findEvent($eventId) ?? $event;
        $left = (int) ($event['places_left'] ?? 0);
        $already = isRegistered($userId, $eventId);
    }
}

require __DIR__ . '/includes/header.php';
?>
    <main>
        <article>
            <h1><?= e((string) $event['title']) ?></h1>
            <p class="club"><?= e((string) $event['club']) ?></p>
            <p><time datetime="<?= e(isoDate((string) $event['starts_at'])) ?>"><?= e(formatTimeRange((string) $event['starts_at'], (string) ($event['ends_at'] ?? ''))) ?></time> — <?= e((string) $event['location']) ?></p>
            <p><?= e(placesLabel($left)) ?></p>

            <p><?= e((string) $event['description']) ?></p>

            <div class="detail-layout">
                <div class="detail-info">
                    <section aria-labelledby="programme">
                        <h2 id="programme">Au programme</h2>
                        <ul>
                            <?php foreach (lines((string) ($event['program'] ?? '')) as $item): ?>
                                <li><?= e($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section aria-labelledby="a-apporter">
                        <h2 id="a-apporter">À apporter</h2>
                        <ul>
                            <?php foreach (lines((string) ($event['to_bring'] ?? '')) as $item): ?>
                                <li><?= e($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                </div>

                <div class="detail-form">
                    <section aria-labelledby="inscription">
                        <h2 id="inscription">Inscription</h2>

                        <?php if (isset($errors['global'])): ?>
                            <p class="field-error" aria-live="polite"><?= e($errors['global']) ?></p>
                        <?php endif; ?>

                        <?php if ($isPast): ?>
                            <p><em>Cet événement est passé, les inscriptions sont fermées.</em></p>
                        <?php elseif ($already): ?>
                            <p class="form-success">Vous êtes inscrit à cet événement.</p>
                            <form method="post" action="annuler-inscription.php">
                                <input type="hidden" name="event_id" value="<?= e((string) $eventId) ?>">
                                <button type="submit" style="background-color:#dc2626;color:white;border:none;padding:var(--sp-xs) var(--sp-sm);border-radius:var(--radius-sm);cursor:pointer;">Annuler mon inscription</button>
                            </form>
                        <?php elseif ($left <= 0): ?>
                            <p><strong>Complet.</strong> Aucune place disponible.</p>
                        <?php elseif ($userId === null): ?>
                            <p>Vous devez être connecté pour vous inscrire.</p>
                            <p><a href="connexion.php?redirect=<?= e(urlencode('evenement.php?id=' . $eventId)) ?>">Se connecter</a> ou <a href="creer-compte.php">créer un compte</a>.</p>
                        <?php else: ?>
                            <form id="registration-form" method="post" action="evenement.php?id=<?= e((string) $eventId) ?>" novalidate>
                                <div>
                                    <label for="motivation">Motivation</label>
                                    <textarea
                                        id="motivation"
                                        name="motivation"
                                        rows="4"
                                        maxlength="300"
                                        placeholder="Pourquoi souhaitez-vous participer à cet événement ?"
                                        <?= isset($errors['motivation']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>
                                    ><?= e($motivation) ?></textarea>
                                    <div class="counter" id="motivation-count"><?= e((string) mb_strlen($motivation)) ?> / 300</div>
                                    <small class="field-error" id="motivation-error" aria-live="polite"><?= e($errors['motivation'] ?? '') ?></small>
                                </div>

                                <div class="checkbox-group">
                                    <div class="checkbox-line">
                                        <input type="checkbox" id="reglement" name="reglement" value="accepte" required <?= isset($errors['reglement']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                                        <label for="reglement">J'accepte le règlement du club <span aria-hidden="true">*</span></label>
                                    </div>
                                    <small class="field-error" id="reglement-error" aria-live="polite"><?= e($errors['reglement'] ?? '') ?></small>
                                </div>

                                <button type="submit">S'inscrire</button>
                                <p class="form-success" hidden aria-live="polite"></p>
                            </form>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </article>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
