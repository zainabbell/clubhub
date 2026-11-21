<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
    http_response_code(404);
    $pageTitle = 'Événement introuvable'; 
    $currentPage = '';
    require __DIR__ . '/includes/header.php';
    echo '<main><p>Événement introuvable. <a href="evenements.php">Retour à la liste des événements</a></p></main>';
    require __DIR__ . '/includes/footer.php'; 
    exit;
}

$event = findEvent((int)$id);
if ($event === null) {
    http_response_code(404);
    $pageTitle = 'Événement introuvable'; 
    $currentPage = '';
    require __DIR__ . '/includes/header.php';
    echo '<main><p>Événement introuvable. <a href="evenements.php">Retour à la liste des événements</a></p></main>';
    require __DIR__ . '/includes/footer.php'; 
    exit;
}

$pageTitle = $event['title']; 
$currentPage = 'evenements';

$registered = isset($_SESSION['registrations'][(int)$id]);
$errors = [];
$level = ''; 
$motivation = ''; 
$agree = false; 
$arduino = 'non';
$u = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $level = trim($_POST['level'] ?? '');
    $motivation = trim($_POST['motivation'] ?? '');
    $agree = isset($_POST['agree']);
    $arduino = trim($_POST['arduino'] ?? 'non');

    if (!in_array($level, ['1', '2', '3'], true)) {
        $errors['level'] = "Veuillez sélectionner un niveau d'études valide.";
    }
    if (mb_strlen($motivation) > 300) {
        $errors['motivation'] = 'La motivation ne doit pas dépasser 300 caractères.';
    }
    if (!$agree) {
        $errors['agree'] = 'Vous devez accepter le règlement du club.';
    }

    if (empty($errors)) {
        $_SESSION['registrations'][(int)$id] = true;
        flash('success', 'Votre inscription à ' . $event['title'] . ' a été confirmée !');
        redirect('evenement.php?id=' . (int)$id);
    }
}

require __DIR__ . '/includes/header.php';
?>
<main>
    <article>
        <h1><?= e($event['title']) ?></h1>
        <p class="club"><?= e($event['club']) ?></p>
        <p><time><?= e($event['date']) ?></time> — <?= e($event['place']) ?></p>
        <p>Places disponibles : <?= e((string)$event['places']) ?></p>
        <p><?= e($event['description']) ?></p>

        <div class="detail-layout">
            <div class="detail-info">
                <?php if (!empty($event['program'])): ?>
                <section aria-labelledby="programme">
                    <h2 id="programme">Au programme</h2>
                    <?php if (is_array($event['program'])): ?>
                        <ul>
                            <?php foreach ($event['program'] as $item): ?>
                                <li><?= e($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <pre style="white-space:pre-wrap;font-family:inherit;"><?= e($event['program']) ?></pre>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <?php if (!empty($event['bring'])): ?>
                <section aria-labelledby="a-apporter">
                    <h2 id="a-apporter">À apporter</h2>
                    <?php if (is_array($event['bring'])): ?>
                        <ul>
                            <?php foreach ($event['bring'] as $item): ?>
                                <li><?= e($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?= e($event['bring']) ?></p>
                    <?php endif; ?>
                </section>
                <?php endif; ?>
            </div>

            <div class="detail-form">
                <section aria-labelledby="inscription">
                    <h2 id="inscription">Inscription</h2>

                    <?php if ($registered): ?>
                        <div class="flash flash-success">
                            <p>Vous êtes déjà inscrit à cet événement.</p>
                        </div>
                    <?php elseif (!$u): ?>
                        <p><a href="connexion.php?next=<?= urlencode('evenement.php?id=' . $event['id']) ?>">Se connecter</a> pour s'inscrire à cet événement.</p>
                    <?php else: ?>
                        <form method="post" action="evenement.php?id=<?= e((string)$event['id']) ?>" novalidate id="registration-form">
                            <p>Connecté en tant que : <strong><?= e($u['name']) ?></strong> (<?= e($u['email']) ?>)</p>

                            <div>
                                <label for="niveau">Niveau d'études <span aria-hidden="true">*</span></label>
                                <select id="niveau" name="level" required class="<?= isset($errors['level']) ? 'is-invalid' : '' ?>">
                                    <option value="">-- Choisir --</option>
                                    <option value="1" <?= $level === '1' ? 'selected' : '' ?>>1re année</option>
                                    <option value="2" <?= $level === '2' ? 'selected' : '' ?>>2e année</option>
                                    <option value="3" <?= $level === '3' ? 'selected' : '' ?>>3e année</option>
                                </select>
                                <?php if (isset($errors['level'])): ?>
                                    <small class="field-error"><?= e($errors['level']) ?></small>
                                <?php endif; ?>
                            </div>

                            <fieldset>
                                <legend>Avez-vous déjà utilisé un Arduino ? <span aria-hidden="true">*</span></legend>
                                <div>
                                    <input type="radio" id="arduino-oui" name="arduino" value="oui" <?= $arduino === 'oui' ? 'checked' : '' ?>>
                                    <label for="arduino-oui">Oui</label>
                                </div>
                                <div>
                                    <input type="radio" id="arduino-non" name="arduino" value="non" <?= $arduino === 'non' ? 'checked' : '' ?>>
                                    <label for="arduino-non">Non</label>
                                </div>
                            </fieldset>

                            <div>
                                <label for="motivation">Motivation</label>
                                <textarea id="motivation" name="motivation" rows="4" maxlength="300" placeholder="Pourquoi souhaitez-vous participer à cet atelier ?"><?= e($motivation) ?></textarea>
                                <small id="motivation-count" class="counter">0 / 300</small>
                                <?php if (isset($errors['motivation'])): ?>
                                    <small class="field-error"><?= e($errors['motivation']) ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="checkbox-group">
                                <div class="checkbox-line">
                                    <input type="checkbox" id="reglement" name="agree" value="1" <?= $agree ? 'checked' : '' ?>>
                                    <label for="reglement">J'accepte le règlement du club <span aria-hidden="true">*</span></label>
                                </div>
                                <?php if (isset($errors['agree'])): ?>
                                    <small class="field-error"><?= e($errors['agree']) ?></small>
                                <?php endif; ?>
                            </div>

                            <button type="submit">S'inscrire</button>
                        </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </article>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
