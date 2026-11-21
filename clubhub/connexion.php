<?php
require_once __DIR__ . '/includes/bootstrap.php';
$email=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']??''); $pw=$_POST['password']??'';
  $u=findUserByEmail($email);
  if(!$u || !password_verify($pw,$u['password'])){ $err='Email ou mot de passe incorrect.'; }
  else { loginUser($u); flash('success','Bonjour '.$u['name'].' !'); redirect(safeNext($_GET['next']??null)); }
}
$pageTitle='Connexion'; $currentPage='';
require __DIR__.'/includes/header.php';
?>
<main class="auth-page"><div class="auth-card">
<h2>Connexion</h2>
<?php if($err):?><div class="flash flash-error"><?=e($err)?></div><?php endif;?>
<form method="post">
<label>Email <input name="email" value="<?=e($email)?>"></label>
<label>Mot de passe <input type="password" name="password"></label>
<button>Se connecter</button>
</form>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
