# Réponses aux questions et défis — ClubHub

---

## Atelier S01 : Formulaires HTML et requêtes HTTP

### 1. Que se passe-t-il si un champ n'a pas d'attribut `name` ?
Lorsqu'un formulaire HTML est soumis, le navigateur rassemble les couples `clé=valeur` correspondant aux champs saisis pour construire le corps (en `POST`) ou la chaîne de requête (en `GET`).
Si un élément de formulaire (`<input>`, `<select>`, `<textarea>`) ne possède pas d'attribut `name`, le navigateur ignore complètement ce champ lors de la soumission : **sa valeur n'est jamais transmise au serveur**. L'attribut `id` sert à l'accessibilité (lien avec le `<label for="...">`) et au ciblage CSS/JS, mais seul l'attribut `name` identifie la donnée pour le protocole HTTP.

### 2. Différence entre `method="post"` et `method="get"` : où sont les données ?
- Avec `method="get"`, les données saisies sont encodées dans l'URL elle-même, sous la forme d'une chaîne de requête : `page.php?nom=Dupont&email=test%40campus.fr`.
- Avec `method="post"`, les données sont encapsulées dans le corps de la requête HTTP (*HTTP request body / Payload*), invisibles dans la barre d'adresse.

### 3. Pourquoi `GET` est-il une très mauvaise idée pour un mot de passe ou des données sensibles ?
1. **Historique du navigateur** : l'URL complète avec le mot de passe en clair reste stockée dans l'historique du navigateur de l'utilisateur.
2. **Journaux des serveurs web et proxies** : les serveurs HTTP (Apache, Nginx, proxies d'entreprise) enregistrent chaque URL demandée dans leurs fichiers d'accès (`access.log`). Des mots de passe en clair y figureraient donc sans protection.
3. **Visibilité à l'écran & partage** : l'URL est visible dans la barre d'adresse par quiconque regarde l'écran, et peut être involontairement copiée/collée dans un message ou un mail.
4. **En-tête `Referer`** : si la page contient un lien externe ou une ressource externe, l'URL complète contenant les données sensibles est envoyée à des sites tiers via l'en-tête HTTP `Referer`.

---

## Atelier S04 : Backend PHP, validation et contournement

### 1. Contourner le JavaScript côté client
- **Désactivation de JavaScript** (DevTools → F12 → Désactiver JavaScript) :
  En soumettant un formulaire vide ou mal formé, le contrôle JS du navigateur ne s'exécute pas. C'est le serveur PHP (`evenement.php`) qui prend le relais : il valide chaque champ obligatoire (`nom ≥ 3`, `filter_var($email, FILTER_VALIDATE_EMAIL)`, `niveau`, `reglement`). Si un champ est invalide, il refuse l'enregistrement et réaffiche le formulaire avec les messages d'erreurs correspondants.
- **Requête directe via `curl`** :
  `curl -X POST -d "name=A&email=faux" "http://localhost:8000/evenement.php?id=1"`
  Le serveur reçoit la requête HTTP brute sans passer par une interface graphique. La validation PHP vérifie l'absence des champs obligatoires et le format incorrect : la requête est rejetée. Cela démontre que **la validation côté client en JavaScript est un confort ergonomique**, mais que **seule la validation côté serveur garantit la sécurité et l'intégrité des données**.

### 2. Rechargement après l'envoi (F5) et pattern Post/Redirect/Get (PRG)
- **Comportement observé sans redirection** :
  Après l'envoi d'un formulaire en POST, appuyer sur F5 incite le navigateur à afficher une boîte de dialogue : « *Confirmer le nouvel envoi du formulaire ?* ».
- **Pourquoi est-ce un problème critique pour un vrai site ?** :
  Si l'utilisateur valide la boîte de dialogue, le navigateur renvoie exactement la même requête POST avec les mêmes données. Sur une plateforme e-commerce ou d'inscription, cela peut provoquer :
  - Un paiement en double,
  - Une double réservation ou décompte erroné des places restantes,
  - Des doublons dans la base de données.
- **Solution (PRG : Post/Redirect/Get)** :
  Le serveur traite le POST, enregistre un message flash en session, puis répond immédiatement avec une redirection HTTP 302 / 303 (`header('Location: ...') ; exit;`). Le navigateur effectue alors une requête GET vers la page cible. Un rafraîchissement avec F5 ne rechargera que la dernière requête GET sans renvoyer de données.

---

## TP3 : Sécurité des sessions et gestion des inscriptions

### 1. Pourquoi `safeNext()` est-elle nécessaire ? (Failles Open Redirect)
Lorsqu'un utilisateur non connecté tente d'accéder à une page protégée, il est redirigé vers `connexion.php?next=compte.php`.
Si la valeur de `next` n'est pas assainie et contrôlée :
Un attaquant peut envoyer un lien malveillant tel que `connexion.php?next=https://site-pirate-hameconnage.com`. L'utilisateur fait confiance au domaine légitime de son université, se connecte, puis est automatiquement redirigé vers le faux site pirate sans s'en rendre compte.
La fonction `safeNext($next)` protège le site en vérifiant que l'URL cible est relative (ne commence ni par `http://`, ni par `https://`, ni par `//`) et correspond à une page interne autorisée.

### 2. Limitation des tentatives de connexion en session : pourquoi est-ce insuffisant ?
Enregistrer le nombre d'échecs de connexion et le timestamp dans la variable `$_SESSION` bloque un utilisateur normal après 5 échecs consécutifs pendant 60 secondes.
Cependant, pour un attaquant utilisant un script automatisé (force brute) :
Il lui suffit de **supprimer son cookie de session (`PHPSESSID`)** ou de ne pas envoyer de cookie à chaque nouvelle requête HTTP. Le serveur crée alors une nouvelle session vide à chaque tentative, avec un compteur remis à zéro.
Une protection efficace contre la force brute doit être stockée de façon centralisée (en base de données ou cache Redis) indexée par adresse IP et/ou par compte ciblé.

### 3. La grande question : Inscription en session vs Base de données
- **Scénario** :
  1. Un étudiant se connecte et s'inscrit à deux événements.
  2. Il se déconnecte, puis se reconnecte.
- **Résultat observé avec le stockage en session (`$_SESSION['registrations']`)** :
  Lors de la déconnexion, `session_destroy()` détruit la session. Toutes les inscriptions disparaissent ! De plus, un organisateur de club ne peut voir que sa propre session et n'a aucun accès aux sessions des autres étudiants.
- **Ce qu'il faut pour un vrai système (implémenté en TP4)** :
  Les inscriptions doivent être persistées dans une table relationnelle en base de données SQL (`registrations(user_id, event_id, created_at)`). Ainsi :
  - Les inscriptions survivent à la déconnexion et au changement d'ordinateur,
  - Une contrainte d'unicité `UNIQUE(user_id, event_id)` empêche les inscriptions en double,
  - Un organisateur peut requêter la liste complète des inscrits via une jointure SQL (`SELECT users.name, users.email FROM registrations JOIN users ... WHERE event_id = ?`).
