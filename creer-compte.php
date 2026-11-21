<?php
require_once __DIR__ . '/includes/bootstrap.php';

$errors = []; 
$name = ''; 
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); 
    $email = trim($_POST['email'] ?? ''); 
    $pw = $_POST['password'] ?? ''; 
    $conf = $_POST['confirm'] ?? '';

    if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
        $errors['name'] = 'Le nom doit comporter entre 3 et 100 caractères.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Veuillez saisir une adresse email valide.';
    } elseif (findUserByEmail($email)) {
        $errors['email'] = 'Cette adresse email est déjà utilisée.';
    }
    if (mb_strlen($pw) < 8) {
        $errors['password'] = 'Le mot de passe doit comporter au moins 8 caractères.';
    }
    if ($pw !== $conf) {
        $errors['confirm'] = 'La confirmation ne correspond pas au mot de passe.';
    }

    if (empty($errors)) {
        addUser($name, $email, password_hash($pw, PASSWORD_DEFAULT)); 
        flash('success', 'Votre compte a été créé avec succès ! Connectez-vous.'); 
        redirect('connexion.php'); 
    }
}

$pageTitle = 'Créer un compte'; 
$currentPage = 'creer-compte';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-page">
    <div class="auth-card" style="max-width: 480px; margin: 2rem auto; background: var(--clr-white); padding: var(--sp-xl); border: 1px solid var(--clr-border); border-radius: var(--radius-md); box-shadow: var(--shadow);">
        <h2>Créer un compte</h2>

        <form method="post" action="creer-compte.php" novalidate style="display:flex; flex-direction:column; gap:var(--sp-md);">
            <div style="display:flex; flex-direction:column; gap:var(--sp-xs);">
                <label for="name">Nom complet <span aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" value="<?= e($name) ?>" required class="<?= isset($errors['name']) ? 'is-invalid' : '' ?>" autocomplete="name">
                <?php if (isset($errors['name'])): ?>
                    <small class="field-error"><?= e($errors['name']) ?></small>
                <?php endif; ?>
            </div>

            <div style="display:flex; flex-direction:column; gap:var(--sp-xs);">
                <label for="email">Email <span aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>" autocomplete="email">
                <?php if (isset($errors['email'])): ?>
                    <small class="field-error"><?= e($errors['email']) ?></small>
                <?php endif; ?>
            </div>

            <div style="display:flex; flex-direction:column; gap:var(--sp-xs);">
                <label for="password">Mot de passe (≥ 8 caractères) <span aria-hidden="true">*</span></label>
                <input type="password" id="password" name="password" required class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>" autocomplete="new-password">
                <?php if (isset($errors['password'])): ?>
                    <small class="field-error"><?= e($errors['password']) ?></small>
                <?php endif; ?>
            </div>

            <div style="display:flex; flex-direction:column; gap:var(--sp-xs);">
                <label for="confirm">Confirmer le mot de passe <span aria-hidden="true">*</span></label>
                <input type="password" id="confirm" name="confirm" required class="<?= isset($errors['confirm']) ? 'is-invalid' : '' ?>" autocomplete="new-password">
                <?php if (isset($errors['confirm'])): ?>
                    <small class="field-error"><?= e($errors['confirm']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" style="align-self:flex-start; padding:var(--sp-sm) var(--sp-xl); background-color:var(--clr-primary); color:white; border:none; border-radius:var(--radius-sm); font-weight:700; cursor:pointer;">Créer mon compte</button>
        </form>

        <p style="margin-top:var(--sp-lg); font-size:0.9rem; color:var(--clr-text-muted);">
            Déjà inscrit ? <a href="connexion.php">Se connecter</a>
        </p>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
