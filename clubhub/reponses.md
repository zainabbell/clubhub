## S04 — ClubHub en PHP

### Défis

#### Contourner le JS
- JavaScript désactivé (F12 → Ctrl+Shift+P → Disable JavaScript) puis envoi d'un formulaire vide : le navigateur peut afficher ses propres validations (`required`, `type="email"`), mais en les contournant/supprimant, la requête POST atteint `evenement.php` qui refuse côté serveur et réaffiche les erreurs (nom, email, niveau, règlement, etc.).
- Requête `curl` :
  `curl -X POST -d "name=A&email=faux" "http://localhost:8000/evenement.php?id=1"`
  Le serveur refuse : `name` n'est pas le champ attendu (`nom` manquant/trop court selon saisie), `email` invalide, `niveau`/`arduino`/`reglement` absents. Les messages d'erreur serveur sont renvoyés et aucune confirmation d'inscription n'est affichée.

#### Recharger après l'envoi (F5)
Après une inscription réussie (POST traité), un F5 déclenche généralement un avertissement du navigateur du type « Confirmer le nouvel envoi du formulaire ? ». Si l'utilisateur confirme, la même requête POST est renvoyée : cela peut provoquer une double inscription/traitement sur un vrai site (problème POST/Redirect/GET, à corriger en séance 5).

#### Recherche côté serveur
`evenements.php?q=arduino` filtre la liste côté PHP (sans JavaScript). Le formulaire de recherche s'envoie en GET ; le filtre JS du TP2 continue de fonctionner quand JS est actif.
