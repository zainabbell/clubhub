<?php
/**
 * ClubHub — demo-injection.php
 * Défi TP4 : Preuve d'injection SQL
 *
 * ATTENTION : Ce script éducatif montre la différence entre une concaténation
 * vulnérable et une requête préparée sécurisée. Ne jamais exposer en production.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$payload = "' OR '1'='1";
$passwordTest = 'nimportequoi';

echo "<h1>Démonstration du test d'injection SQL (' OR '1'='1)</h1>";
echo "<p>Payload testé : <code>" . htmlspecialchars($payload) . "</code></p>";

// -------------------------------------------------------------
// 1. VERSION VULNÉRABLE (Concaténation sans requête préparée)
// -------------------------------------------------------------
echo "<h2>1. Version VULNÉRABLE (concaténation brute)</h2>";
$sqlVuln = "SELECT * FROM users WHERE email = '$payload' LIMIT 1";
echo "<p>Requête SQL exécutée : <code>" . htmlspecialchars($sqlVuln) . "</code></p>";

try {
    $stmtVuln = db()->query($sqlVuln);
    $userVuln = $stmtVuln->fetch();
    if ($userVuln) {
        echo "<p style='color:red;'><strong>DANGER : Injection réussie !</strong> Le premier utilisateur trouvé a été extrait : " . htmlspecialchars($userVuln['email']) . " (ID: " . $userVuln['id'] . ").</p>";
    }
} catch (Throwable $e) {
    echo "<p>Erreur SQL : " . htmlspecialchars($e->getMessage()) . "</p>";
}

// -------------------------------------------------------------
// 2. VERSION SÉCURISÉE (Requête préparée avec PDO)
// -------------------------------------------------------------
echo "<h2>2. Version SÉCURISÉE (Requête préparée)</h2>";
$sqlSec = "SELECT * FROM users WHERE email = ? LIMIT 1";
echo "<p>Requête SQL préparée : <code>" . htmlspecialchars($sqlSec) . "</code> avec paramètre <code>" . htmlspecialchars($payload) . "</code></p>";

$stmtSec = db()->prepare($sqlSec);
$stmtSec->execute([$payload]);
$userSec = $stmtSec->fetch();

if ($userSec === false) {
    echo "<p style='color:green;'><strong>SÉCURISÉ : Aucun résultat trouvé.</strong> MySQL a cherché littéralement l'adresse email <code>' OR '1'='1</code> sans exécuter la syntaxe comme du code SQL.</p>";
} else {
    echo "<p>Utilisateur trouvé.</p>";
}
