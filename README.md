# ClubHub — TP4 (MySQL & PDO)

## Binôme
- **Zainab BELAKHAL**
- *(Binôme)*

---

## 1. Installation de la base de données (Laragon / MySQL)

Pour initialiser la base de données et charger les données de test :

### Étape 1 : Créer la base et l'utilisateur dans MySQL
```sql
CREATE DATABASE clubhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'clubhub'@'localhost' IDENTIFIED BY 'clubhub';
GRANT ALL PRIVILEGES ON clubhub.* TO 'clubhub'@'localhost';
FLUSH PRIVILEGES;
```

### Étape 2 : Importer le schéma des tables
```bash
mysql -u clubhub -p clubhub < database/schema.sql
```

### Étape 3 : Importer les données de test (Seed)
```bash
mysql -u clubhub -p clubhub < database/seed.sql
```

*(Ou via phpMyAdmin : sélectionner la base `clubhub` puis onglet **Importer** pour les deux fichiers `database/schema.sql` puis `database/seed.sql`).*

### Configuration locale (`config.php`)
Copier `config.example.php` en `config.php` et adapter les identifiants si besoin (Laragon : hôte `127.0.0.1`, port `3306`, utilisateur `root` ou `clubhub`). Le fichier `config.php` est exclu du suivi Git grâce au `.gitignore`.

**Comptes de démonstration créés par le seed :**
- 7 utilisateurs de test avec le mot de passe : `demo1234`
- Exemples : `alice@clubhub.test` (étudiante), `sarra@clubhub.test`, `robotique@clubhub.test` (organisateur).

---

## 2. Question 5 — Test d'injection SQL (`' OR '1'='1`)

Si un utilisateur saisit l'email `' OR '1'='1` et un mot de passe quelconque :
- **Résultat observé (version sécurisée)** : la tentative de connexion échoue avec le message générique *« Email ou mot de passe incorrect. »*. Aucune session n'est ouverte et aucune information de la base n'est divulguée.
- **Pourquoi (explication technique)** :
  Dans notre code (`includes/users.php`), la recherche utilise une requête préparée :
  ```php
  $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  ```
  Le moteur MySQL compile d'abord la structure syntaxique de la requête SQL avec le marqueur de paramètre `?`. Ensuite, la valeur de `$email` est envoyée séparément comme une **valeur littérale brute** (une chaîne de caractères) et non comme du code exécutable. La chaîne `' OR '1'='1` n'altère donc pas la logique booléenne de la clause `WHERE` : MySQL cherche littéralement un utilisateur dont l'adresse email est exactement `' OR '1'='1`.
  
  *À l'inverse, dans une version vulnérable par concaténation (`"... WHERE email = '$email'"`), l'entrée deviendrait `"... WHERE email = '' OR '1'='1' LIMIT 1"`, rendant la condition toujours vraie et authentifiant l'attaquant sur le premier compte utilisateur de la table.*

---

## 3. Fonctionnalités réalisées (Base & Plus)

### Architecture et Connexion PDO (`includes/db.php` & `includes/bootstrap.php`)
- Instance unique PDO via `db()` configurée avec :
  - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` (gestion des erreurs par exceptions)
  - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` (tableaux associatifs)
  - `PDO::ATTR_EMULATE_PREPARES => false` (vraies requêtes préparées au niveau MySQL)
  - `charset=utf8mb4` (support complet des accents et emojis sans corruption)
- Toutes les requêtes SQL sont centralisées dans les fichiers de services : `includes/events.php`, `includes/users.php`, `includes/registrations.php`. **Aucune requête SQL n'est écrite directement dans les pages.**
- Les anciens fichiers plats `data/events.php` et `data/users.json` ont été supprimés.

### Événements (`includes/events.php`)
- `getEvents(?int $limit = null)` : retourne les événements futurs (`starts_at >= NOW()`) ordonnés chronologiquement, avec calcul automatique des places restantes.
- `findEvent(int $id)` : recherche par requête préparée.
- Affichage des dates et plages horaires via `formatDate()` et `formatTimeRange()`.
- Découpage du programme et du matériel à apporter ligne par ligne via la fonction `lines()`.

### Gestion des utilisateurs (`includes/users.php`)
- Fonctions `findUserByEmail`, `findUserById`, `addUser` et `verifyUserPassword` entièrement basées sur des requêtes préparées PDO.
- Mots de passe systématiquement hachés avec `password_hash($pw, PASSWORD_DEFAULT)` et vérifiés avec `password_verify()`.

### Inscriptions en base de données (`includes/registrations.php`)
- Les inscriptions sont persistées dans la table MySQL `registrations` et **survivent à la déconnexion** de l'utilisateur.
- **Places restantes calculées en SQL** : `(e.capacity - COUNT(r.id)) AS places_left` via `LEFT JOIN registrations r ON r.event_id = e.id GROUP BY e.id`.
- Si un événement est complet (`places_left <= 0`), le badge « Complet » s'affiche, le formulaire est masqué et le serveur refuse toute nouvelle inscription.
- **Gestion des doublons (Point 8)** : la contrainte `UNIQUE(user_id, event_id)` de la base empêche toute double inscription. La `PDOException` de code `'23000'` est interceptée pour afficher un message utilisateur clair au lieu d'une erreur brute.
- **Annulation sûre (Point 9)** : la requête de suppression dans `cancelRegistration()` utilise obligatoirement `DELETE FROM registrations WHERE user_id = ? AND event_id = ?`. Même si un utilisateur tente de modifier l'identifiant dans les DevTools, il est impossible de supprimer l'inscription d'un tiers.

---

## 4. Défis choisis (jusqu'à 20/20)

### 1. La dernière place (Transaction et `SELECT ... FOR UPDATE`)
- **Problème de concurrence (*Race Condition*)** : Si deux utilisateurs A et B soumettent en même temps leur inscription alors qu'il ne reste qu'une seule place disponible, sans verrouillage, les deux processus lisent `places_left = 1` en parallèle et insèrent tous deux leur inscription, provoquant un dépassement de capacité (*overbooking*).
- **Solution implémentée** : Dans `registerUser()` (`includes/registrations.php`), l'opération est enveloppée dans une transaction PDO (`beginTransaction()`) avec un verrou exclusif sur la ligne de l'événement :
  ```sql
  SELECT e.capacity, COUNT(r.id) AS registered_count
  FROM events e
  LEFT JOIN registrations r ON r.event_id = e.id
  WHERE e.id = ?
  GROUP BY e.id
  FOR UPDATE;
  ```
  Le premier utilisateur verrouille la ligne ; le second attend que la transaction du premier se termine (`commit`). Lorsque le second lit à son tour la ligne, le compteur a été incrémenté et la fonction lui renvoie immédiatement `'full'`.

### 2. Recherche côté serveur (`evenements.php?q=...`)
- Dans `includes/events.php`, la fonction `searchEvents(string $q)` exécute une requête préparée avec `LIKE ?` sur le titre, le club, la description et le lieu :
  ```sql
  WHERE e.starts_at >= NOW()
    AND (e.title LIKE ? OR e.club LIKE ? OR e.description LIKE ? OR e.location LIKE ?)
  ```
- Le formulaire de filtres fonctionne parfaitement en méthode `GET` même lorsque JavaScript est totalement désactivé (balise `<noscript>` et soumission native).

### 3. Gestion des événements passés dans « Mon compte »
- Dans `mon-compte.php`, les inscriptions de l'utilisateur sont séparées dynamiquement en deux sections : **« Mes inscriptions — À venir »** et **« Mes inscriptions — Passées »**.
- Côté serveur, `cancelRegistration()` vérifie la date de l'événement et refuse formellement l'annulation si la date de début est antérieure à l'instant présent (`starts_at < NOW()`).

### 4. Démonstration de preuve d'injection (`demo-injection.php`)
- Un fichier autonome [demo-injection.php](file:///c:/1%20ING1-5/dev%20web/clubhub/demo-injection.php) a été créé pour illustrer concrètement la différence entre une concaténation vulnérable (où le payload `' OR '1'='1` réussit à extraire le premier utilisateur) et la requête préparée PDO (qui neutralise complètement l'attaque).
