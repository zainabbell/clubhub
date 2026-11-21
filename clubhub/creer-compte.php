<?php
require_once __DIR__ . '/includes/bootstrap.php';
$errors=[]; $name=''; $email='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $pw=$_POST['password']??''; $conf=$_POST['confirm']??'';
  if(strlen($name)<3||strlen($name)>100) $errors['name']='Nom 3-100 caractères';
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors['email']='Email invalide';
  elseif(findUserByEmail($email)) $errors['email']='Email déjà utilisé';
  if(strlen($pw)<8) $errors['password']='Mot de passe ≥ 8';
  if($pw!==$conf) $errors['confirm']='Confirmation différente';
  if(empty($errors)){ addUser($name,$email,password_hash($pw,PASSWORD_DEFAULT)); flash('success','Votre compte est créé'); redirect('connexion.php'); }
}
$pageTitle='Créer un compte'; $currentPage='';
require __DIR__.'/includes/header.php';
?>
<main class="auth-page"><div class="auth-card">
<h2>Créer un compte</h2>
<form method="post">
<label>Nom <input name="name" value="<?=e($name)?>"></label><?php if(isset($errors['name'])):?><span><?=e($errors['name'])?></span><?php endif;?>
<label>Email <input name="email" value="<?=e($email)?>"></label><?php if(isset($errors['email'])):?><span><?=e($errors['email'])?></span><?php endif;?>
<label>Mot de passe <input type="password" name="password"></label><?php if(isset($errors['password'])):?><span><?=e($errors['password'])?></span><?php endif;?>
<label>Confirmation <input type="password" name="confirm"></label><?php if(isset($errors['confirm'])):?><span><?=e($errors['confirm'])?></span><?php endif;?>
<button>Créer</button>
</form>
</div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
