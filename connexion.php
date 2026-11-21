<?php
require_once __DIR__ . '/includes/bootstrap.php';

$email = ''; 
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); 
    $pw = $_POST['password'] ?? '';
    $u = findUserByEmail($email);
    
    if (!$u || !password_verify($pw, $u['password'])) { 
        $err = 'Email ou mot de passe incorrect.'; 
    } else { 
        loginUser($u); 
        flash('success', 'Bonjour ' . $u['name'] . ' !'); 
        redirect(safeNext($_GET['next'] ?? null)); 
    }
}

$pageTitle = 'Connexion'; 
$currentPage = 'connexion';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-page">
    <div class="auth-card" style="max-width: 450px; margin: 2rem auto; background: var(--clr-white); padding: var(--sp-xl); border: 1px solid var(--clr-border); border-radius: var(--radius-md); box-shadow: var(--shadow);">
        <h2>Connexion</h2>
        <?php if ($err): ?>
            <div class="flash flash-error" style="background:#fee2e2; color:#b91c1c; padding:var(--sp-sm) var(--sp-md); border-radius:var(--radius-sm); margin-bottom:var(--sp-md); border:1px solid #f87171;">
                <?= e($err) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="connexion.php<?= isset($_GET['next']) ? '?next=' . urlencode($_GET['next']) : '' ?>" style="display:flex; flex-direction:column; gap:var(--sp-md);">
            <div style="display:flex; flex-direction:column; gap:var(--sp-xs);">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
            </div>

            <div style="display:flex; flex-direction:column; gap:var(--sp-xs);">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" style="align-self:flex-start; padding:var(--sp-sm) var(--sp-xl); background-color:var(--clr-primary); color:white; border:none; border-radius:var(--radius-sm); font-weight:700; cursor:pointer;">Se connecter</button>
        </form>

        <p style="margin-top:var(--sp-lg); font-size:0.9rem; color:var(--clr-text-muted);">
            Pas encore de compte ? <a href="creer-compte.php">Créer un compte</a>
        </p>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
