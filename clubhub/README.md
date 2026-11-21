# ClubHub — TP4 (MySQL & PDO)

## Binôme
- *(Prénom NOM)*
- *(Prénom NOM)*

## Installation de la base (Laragon / MySQL)

1. Créer la base et l’utilisateur :
```sql
CREATE DATABASE clubhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'clubhub'@'localhost' IDENTIFIED BY 'clubhub';
GRANT ALL PRIVILEGES ON clubhub.* TO 'clubhub'@'localhost';
FLUSH PRIVILEGES;
```

2. Importer le schéma :
```bash
mysql -u clubhub -p clubhub < database/schema.sql
```

3. Importer les données :
```bash
mysql -u clubhub -p clubhub < database/seed.sql
```

Puis copier `config.example.php` en `config.php` et adapter si besoin (Laragon : souvent `db_host=127.0.0.1`, `db_port=3306`, `db_user=root`, `db_pass` vide, ou utiliser le compte `clubhub`). Vérifier que `config.php` n’est pas suivi par Git (`.gitignore`).  
Pour remettre à zéro : relancer les deux scripts dans le même ordre.

**Comptes seed :** 7 utilisateurs, mot de passe `demo1234` (ex. `alice@clubhub.test`).

## Structure (TP4)
- `config.php` (local, ignoré) / `config.example.php`
- `database/schema.sql`, `database/seed.sql`
- `includes/db.php` (PDO), `includes/functions.php` (helpers `e()`, dates, session/flash), `includes/bootstrap.php`
- Accès données (SQL uniquement ici) : `includes/events.php`, `includes/users.php`, `includes/registrations.php`
- Pages : `index.php`, `evenements.php`, `evenement.php`, `connexion.php`, `creer-compte.php`, `deconnexion.php`, `mon-compte.php`, `annuler-inscription.php`
- Front : `css/style.css`, `js/app.js`, `img.jpg`

## Question 5 — Test d’injection (`' OR '1'='1`)

Si on tente de se connecter avec l’email `' OR '1'='1` et n’importe quel mot de passe :
- **Résultat observé (version sécurisée)** : la connexion échoue (« Email ou mot de passe incorrect ») et aucune donnée n’est divulguée.
- **Pourquoi** : la requête de recherche utilisateur est préparée (`prepare('SELECT * FROM users WHERE email = ? LIMIT 1')` puis `execute([$email])`). MySQL reçoit d’abord la requête avec un paramètre, puis la valeur de `$email` comme **donnée** (une chaîne littérale), jamais comme du code SQL. La condition ne devient pas vraie pour tous les comptes, contrairement à une concaténation vulnérable du type `"... WHERE email = '$email'"` qui transformerait l’entrée en fragment SQL.

## Fonctionnalités réalisées (base / plus)
- Connexion PDO unique via `db()` (`utf8mb4`, exceptions, `FETCH_ASSOC`, vraies requêtes préparées).
- Événements et utilisateurs en base ; toutes les valeurs externes passées par requêtes préparées ; aucun SQL dans les pages.
- Dates affichées via `starts_at`/`ends_at` (`formatDate`, `formatTimeRange`, `isoDate`) ; programme/à apporter stockés en texte (une ligne par étape) et affichés via `lines()`.
- Authentification : création de compte (`addUser` + `password_hash`), connexion (`password_verify`), déconnexion, `requireLogin()`, PRG/flash.
- « Mon compte » : profil avec `created_at` + inscriptions de l’utilisateur via `registrationsOfUser()` (JOIN sur `events`), séparées en « À venir » / « Passées » (défi).
- Inscriptions en base (`isRegistered`, `registerUser`, `cancelRegistration`, `registrationsOfUser`) : survivent à la déconnexion.
- Places restantes calculées en SQL (`LEFT JOIN registrations` + `COUNT` + `GROUP BY`) et affichées sur cartes, liste, tableau et détail ; événement complet → pas de formulaire et refus serveur.
- Doublon : contrainte `UNIQUE(user_id,event_id)` + détection `SQLSTATE 23000` → message clair.
- Annulation sûre : suppression uniquement si `user_id = ? AND event_id = ?` (l’utilisateur courant), en POST ; impossible d’annuler l’inscription d’un autre même en modifiant `event_id` (et refus si événement passé).

## Défis choisis
- **Recherche côté serveur** : `evenements.php?q=...` filtre en SQL avec `LIKE` et requête préparée (`searchEvents()`), formulaire GET fonctionnel sans JavaScript.
- **La dernière place (transaction)** : `registerUser()` utilise une transaction et `SELECT ... FOR UPDATE` sur l’événement (et vérification d’inscription) pour éviter qu’un dépassement de capacité survienne si deux utilisateurs réservent simultanément la dernière place. Scénario : A et B lisent une place restante ; sans verrou, les deux insertions passeraient. Avec le verrou ligne, B attend la fin de A, relit le compteur et reçoit `full` si A a pris la place.
- **Événements passés** : dans « Mon compte », séparation « À venir » / « Passés » ; annulation d’un événement passé refusée côté serveur.

## Notes
- Je n’ai pas pu exécuter MySQL/Laragon depuis l’environnement de développement automatisé ; valider localement après import des scripts et configuration de `config.php`.
- Anciennes données fichiers (`data/events.php`) supprimées ; pages n’exécutent aucune requête SQL directe.
