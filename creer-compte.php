<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'ClubHub - Créer un compte';
$currentPage = 'creer-compte';
$errors = [];
$values = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name'] = trim((string) ($_POST['name'] ?? ''));
    $values['email'] = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if (mb_strlen($values['name']) < 3 || mb_strlen($values['name']) > 100) {
        $errors['name'] = 'Le nom doit comporter entre 3 et 100 caractères.';
    }
    if ($values['email'] === '' || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Veuillez saisir une adresse email valide.';
    }
    if (mb_strlen($password) < 8) {
        $errors['password'] = 'Le mot de passe doit comporter au moins 8 caractères.';
    }
    if ($password !== $confirm) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
    }

    if ($errors === []) {
        try {
            $id = addUser($values['name'], $values['email'], $password);
            $_SESSION['user_id'] = $id;
            flash('success', 'Votre compte est créé ! Bienvenue ' . $values['name'] . '.');
            redirect('mon-compte.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors['email'] = 'Un compte existe déjà avec cet email.';
            } else {
                throw $exception;
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
    <main class="auth-page">
        <div class="auth-card" style="max-width:480px;margin:2rem auto;background:var(--clr-white);padding:var(--sp-xl);border:1px solid var(--clr-border);border-radius:var(--radius-md);box-shadow:var(--shadow);">
            <h2>Créer un compte</h2>

            <form method="post" action="creer-compte.php" novalidate id="registration-form" style="display:flex;flex-direction:column;gap:var(--sp-md);">
                <div style="display:flex;flex-direction:column;gap:var(--sp-xs);">
                    <label for="name">Nom complet <span aria-hidden="true">*</span></label>
                    <input type="text" id="name" name="name" required value="<?= e($values['name']) ?>" <?= isset($errors['name']) ? 'class="is-invalid" aria-invalid="true"' : '' ?> autocomplete="name">
                    <small class="field-error" aria-live="polite"><?= e($errors['name'] ?? '') ?></small>
                </div>

                <div style="display:flex;flex-direction:column;gap:var(--sp-xs);">
                    <label for="email">Email <span aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" required value="<?= e($values['email']) ?>" <?= isset($errors['email']) ? 'class="is-invalid" aria-invalid="true"' : '' ?> autocomplete="email">
                    <small class="field-error" id="email-error" aria-live="polite"><?= e($errors['email'] ?? '') ?></small>
                </div>

                <div style="display:flex;flex-direction:column;gap:var(--sp-xs);">
                    <label for="password">Mot de passe (≥ 8 caractères) <span aria-hidden="true">*</span></label>
                    <input type="password" id="password" name="password" required <?= isset($errors['password']) ? 'class="is-invalid" aria-invalid="true"' : '' ?> autocomplete="new-password">
                    <small class="field-error" aria-live="polite"><?= e($errors['password'] ?? '') ?></small>
                </div>

                <div style="display:flex;flex-direction:column;gap:var(--sp-xs);">
                    <label for="password_confirm">Confirmer le mot de passe <span aria-hidden="true">*</span></label>
                    <input type="password" id="password_confirm" name="password_confirm" required <?= isset($errors['password_confirm']) ? 'class="is-invalid" aria-invalid="true"' : '' ?> autocomplete="new-password">
                    <small class="field-error" aria-live="polite"><?= e($errors['password_confirm'] ?? '') ?></small>
                </div>

                <button type="submit" style="align-self:flex-start;padding:var(--sp-sm) var(--sp-xl);background-color:var(--clr-primary);color:white;border:none;border-radius:var(--radius-sm);font-weight:700;cursor:pointer;">Créer mon compte</button>
            </form>

            <p style="margin-top:var(--sp-lg);font-size:0.9rem;color:var(--clr-text-muted);">
                Déjà inscrit ? <a href="connexion.php">Se connecter</a>.
            </p>
        </div>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
