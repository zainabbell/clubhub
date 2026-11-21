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
            flash('success', 'Bonjour ' . (string) $user['name'] . ' !');
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
    <main class="auth-page">
        <div class="auth-card" style="max-width:450px;margin:2rem auto;background:var(--clr-white);padding:var(--sp-xl);border:1px solid var(--clr-border);border-radius:var(--radius-md);box-shadow:var(--shadow);">
            <h2>Connexion</h2>
            <?php if (isset($errors['global'])): ?>
                <p class="field-error" style="background:#fee2e2;color:#b91c1c;padding:var(--sp-sm) var(--sp-md);border-radius:var(--radius-sm);margin-bottom:var(--sp-md);border:1px solid #f87171;" aria-live="polite"><?= e($errors['global']) ?></p>
            <?php endif; ?>

            <form method="post" action="connexion.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode((string) $_GET['redirect']) : '' ?>" novalidate id="registration-form" style="display:flex;flex-direction:column;gap:var(--sp-md);">
                <div style="display:flex;flex-direction:column;gap:var(--sp-xs);">
                    <label for="email">Email <span aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" required autocomplete="email" value="<?= e($email) ?>" <?= isset($errors['email']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                    <small class="field-error" id="email-error" aria-live="polite"><?= e($errors['email'] ?? '') ?></small>
                </div>

                <div style="display:flex;flex-direction:column;gap:var(--sp-xs);">
                    <label for="password">Mot de passe <span aria-hidden="true">*</span></label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" <?= isset($errors['password']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                    <small class="field-error" aria-live="polite"><?= e($errors['password'] ?? '') ?></small>
                </div>

                <button type="submit" style="align-self:flex-start;padding:var(--sp-sm) var(--sp-xl);background-color:var(--clr-primary);color:white;border:none;border-radius:var(--radius-sm);font-weight:700;cursor:pointer;">Se connecter</button>
            </form>

            <p style="margin-top:var(--sp-lg);font-size:0.9rem;color:var(--clr-text-muted);">
                Pas encore de compte ? <a href="creer-compte.php">Créer un compte</a>.
            </p>
            <p style="margin-top:var(--sp-xs);font-size:0.85rem;color:var(--clr-text-muted);">
                Comptes de démonstration : mot de passe <code>demo1234</code> (ex. <code>alice@clubhub.test</code>).
            </p>
        </div>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
