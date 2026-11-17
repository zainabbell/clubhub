<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'ClubHub - Connexion';
$currentPage = 'connexion';
$errors = [];
$email = trim((string) ($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Veuillez saisir une adresse email valide.';
    }
    if ($password === '') {
        $errors['password'] = 'Veuillez saisir votre mot de passe.';
    }

    if ($errors === []) {
        $user = findUserByEmail($email);
        if ($user === null || !verifyUserPassword($user, $password)) {
            $errors['global'] = 'Email ou mot de passe incorrect.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];
            $redirect = (string) ($_GET['redirect'] ?? 'mon-compte.php');
            if ($redirect === '' || str_starts_with($redirect, 'http')) {
                $redirect = 'mon-compte.php';
            }
            redirect($redirect);
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
    <main>
        <h1>Connexion</h1>
        <?php if (isset($errors['global'])): ?>
            <p class="field-error" aria-live="polite"><?= e($errors['global']) ?></p>
        <?php endif; ?>
        <form method="post" action="connexion.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode((string) $_GET['redirect']) : '' ?>" novalidate id="registration-form">
            <div>
                <label for="email">Email <span aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email" required autocomplete="email" value="<?= e($email) ?>" <?= isset($errors['email']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                <small class="field-error" id="email-error" aria-live="polite"><?= e($errors['email'] ?? '') ?></small>
            </div>
            <div>
                <label for="password">Mot de passe <span aria-hidden="true">*</span></label>
                <input type="password" id="password" name="password" required autocomplete="current-password" <?= isset($errors['password']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                <small class="field-error" aria-live="polite"><?= e($errors['password'] ?? '') ?></small>
            </div>
            <button type="submit">Se connecter</button>
            <p class="form-success" hidden aria-live="polite"></p>
        </form>
        <p>Pas encore de compte ? <a href="creer-compte.php">Créer un compte</a>.</p>
        <p>Comptes seed : mot de passe <code>demo1234</code> (ex. <code>alice@clubhub.test</code>).</p>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
