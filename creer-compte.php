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

    if (mb_strlen($values['name']) < 2) {
        $errors['name'] = 'Le nom doit comporter au moins 2 caractères.';
    }
    if ($values['email'] === '' || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Veuillez saisir une adresse email valide.';
    }
    if (mb_strlen($password) < 4) {
        $errors['password'] = 'Le mot de passe doit comporter au moins 4 caractères.';
    }
    if ($password !== $confirm) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
    }

    if ($errors === []) {
        try {
            $id = addUser($values['name'], $values['email'], $password);
            $_SESSION['user_id'] = $id;
            flash('success', 'Compte créé avec succès. Bienvenue !');
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
    <main>
        <h1>Créer un compte</h1>
        <form method="post" action="creer-compte.php" novalidate id="registration-form">
            <div>
                <label for="name">Nom <span aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" required value="<?= e($values['name']) ?>" <?= isset($errors['name']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                <small class="field-error" aria-live="polite"><?= e($errors['name'] ?? '') ?></small>
            </div>
            <div>
                <label for="email">Email <span aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email" required autocomplete="email" value="<?= e($values['email']) ?>" <?= isset($errors['email']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                <small class="field-error" id="email-error" aria-live="polite"><?= e($errors['email'] ?? '') ?></small>
            </div>
            <div>
                <label for="password">Mot de passe <span aria-hidden="true">*</span></label>
                <input type="password" id="password" name="password" required autocomplete="new-password" <?= isset($errors['password']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                <small class="field-error" aria-live="polite"><?= e($errors['password'] ?? '') ?></small>
            </div>
            <div>
                <label for="password_confirm">Confirmer le mot de passe <span aria-hidden="true">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password" <?= isset($errors['password_confirm']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
                <small class="field-error" aria-live="polite"><?= e($errors['password_confirm'] ?? '') ?></small>
            </div>
            <button type="submit">Créer le compte</button>
            <p class="form-success" hidden aria-live="polite"></p>
        </form>
        <p>Déjà un compte ? <a href="connexion.php">Se connecter</a>.</p>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
