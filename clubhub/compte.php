<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$u=currentUser();
$pageTitle='Mon compte'; $currentPage='';
require __DIR__.'/includes/header.php';
?>
<main>
<h2>Mon compte</h2>
<p>Nom: <?=e($u['name'])?></p>
<p>Email: <?=e($u['email'])?></p>
<p>Rôle: <?=e($u['role'])?></p>
<h3>Mes inscriptions</h3>
<?php $regs=$_SESSION['registrations']??[]; foreach($regs as $eid=>$v): $ev=findEvent((int)$eid); if(!$ev) continue; ?>
<p><?=e($ev['title'])?> - <?=e($ev['date'])?> - <?=e($ev['place'])?> <a href="evenement.php?id=<?=e((string)$ev['id'])?>">voir</a>
<form method="post" action="annuler.php" style="display:inline"><input type="hidden" name="id" value="<?=e((string)$ev['id'])?>"><button>Annuler</button></form>
</p>
<?php endforeach; ?>
</main>
<?php require __DIR__.'/includes/footer.php'; ?>
