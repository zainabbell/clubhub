<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();

$u = currentUser();
$pageTitle = 'Mon compte';
$currentPage = 'compte';

require __DIR__ . '/includes/header.php';
?>
<main>
    <h1>Mon compte</h1>

    <section aria-labelledby="profil" style="margin-bottom: var(--sp-xl);">
        <h2 id="profil">Profil</h2>
        <p><strong>Nom :</strong> <?= e($u['name']) ?></p>
        <p><strong>Email :</strong> <?= e($u['email']) ?></p>
        <p><strong>Rôle :</strong> <?= e($u['role']) ?></p>
    </section>

    <section aria-labelledby="inscriptions">
        <h2 id="inscriptions">Mes inscriptions</h2>
        <?php 
        $regs = $_SESSION['registrations'] ?? [];
        if (empty($regs)): 
        ?>
            <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
        <?php else: ?>
            <div class="cartes">
                <?php 
                foreach ($regs as $eid => $v): 
                    $ev = findEvent((int)$eid); 
                    if (!$ev) continue; 
                ?>
                    <article>
                        <p class="club"><?= e($ev['club']) ?></p>
                        <h3><a href="evenement.php?id=<?= e((string)$ev['id']) ?>"><?= e($ev['title']) ?></a></h3>
                        <p><time><?= e($ev['date']) ?></time> — <?= e($ev['place'] ?? 'Campus') ?></p>
                        <p>Places : <?= e((string)$ev['places']) ?></p>
                        <div class="card-footer" style="display:flex; justify-content:space-between; align-items:center;">
                            <a href="evenement.php?id=<?= e((string)$ev['id']) ?>">Voir</a>
                            <form method="post" action="annuler.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= e((string)$ev['id']) ?>">
                                <button type="submit" style="background-color:#dc2626; color:white; border:none; padding:var(--sp-xs) var(--sp-sm); border-radius:var(--radius-sm); cursor:pointer;">Annuler</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
