<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
    http_response_code(404);
    $pageTitle = 'Introuvable'; $currentPage = '';
    require __DIR__ . '/includes/header.php';
    echo '<main><p>Événement introuvable. <a href="evenements.php">Retour à la liste</a></p></main>';
    require __DIR__ . '/includes/footer.php'; exit;
}
$event = findEvent((int)$id);
if ($event === null) {
    http_response_code(404);
    $pageTitle = 'Introuvable'; $currentPage = '';
    require __DIR__ . '/includes/header.php';
    echo '<main><p>Événement introuvable. <a href="evenements.php">Retour à la liste</a></p></main>';
    require __DIR__ . '/includes/footer.php'; exit;
}
$pageTitle = $event['title']; $currentPage = 'evenements';

$registered = isset($_SESSION['registrations'][(int)$id]);
$errors = [];
$name = $email = $level = $motivation = $bring = ''; $agree = false; $yesno = '';
$u = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $level = trim($_POST['level'] ?? '');
    $motivation = trim($_POST['motivation'] ?? '');
    $bring = trim($_POST['bring'] ?? '');
    $agree = isset($_POST['agree']);
    $yesno = trim($_POST['yesno'] ?? '');
    if (!in_array($level, ['1','2','3'], true)) $errors['level'] = 'Niveau invalide';
    if (strlen($motivation) > 300) $errors['motivation'] = 'Max 300 caractères';
    if (!$agree) $errors['agree'] = 'Règlement à accepter';
    if (empty($errors)) {
        $_SESSION['registrations'][(int)$id] = true;
        flash('success', 'Inscription confirmée');
        redirect('evenement.php?id=' . (int)$id);
    }
}

require __DIR__ . '/includes/header.php';
?>
<main>
<h2><?= e($event['title']) ?></h2>
<p><?= e($event['club']) ?> - <?= e($event['date']) ?> - <?= e($event['place']) ?> - <?= e((string)$event['places']) ?> places</p>
<p><?= e($event['description']) ?></p>
<h3>Programme</h3>
<pre><?= e($event['program']) ?></pre>
<h3>À apporter</h3>
<p><?= e($event['bring']) ?></p>

<?php if ($registered): ?>
<p>Vous êtes déjà inscrit.</p>
<?php elseif (!$u): ?>
<p><a href="connexion.php?next=evenement.php?id=<?= e((string)$event['id']) ?>">Se connecter</a> pour s'inscrire.</p>
<?php else: ?>
<form method="post" action="evenement.php?id=<?= e((string)$event['id']) ?>">
<label>Niveau
<select name="level">
<option value="">--</option>
<?php foreach (['1','2','3'] as $l): ?>
<option value="<?= e($l) ?>" <?= $level===$l?'selected':'' ?>><?= e($l) ?></option>
<?php endforeach; ?>
</select></label>
<?php if (isset($errors['level'])): ?><span><?= e($errors['level']) ?></span><?php endif; ?>
<label>Motivation <textarea name="motivation"><?= e($motivation) ?></textarea></label>
<?php if (isset($errors['motivation'])): ?><span><?= e($errors['motivation']) ?></span><?php endif; ?>
<label>À apporter <input type="text" name="bring" value="<?= e($bring) ?>"></label>
<label><input type="checkbox" name="agree" <?= $agree?'checked':'' ?>> J'accepte le règlement</label>
<?php if (isset($errors['agree'])): ?><span><?= e($errors['agree']) ?></span><?php endif; ?>
<label>Réponse
<select name="yesno">
<option value="oui" <?= $yesno==='oui'?'selected':'' ?>>oui</option>
<option value="non" <?= $yesno==='non'?'selected':'' ?>>non</option>
</select></label>
<button type="submit">S'inscrire</button>
</form>
<?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
