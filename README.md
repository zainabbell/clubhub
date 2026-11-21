# ClubHub — Plateforme des clubs étudiants

Ce projet regroupe les étapes de développement du site **ClubHub**, de la version HTML/CSS/JS statique et interactive (Atelier S01, TP1, TP2) jusqu'à la version dynamique sécurisée avec sessions, authentification et base de données (Atelier S04, TP3, TP4).

---

## Binôme
- **Zainab BELAKHAL**
- **Binôme**

---

## 1. Atelier S01 & TP1 : Styliser ClubHub (CSS & Responsive)

### Objectifs
- Écriture d'un balisage HTML5 sémantique et valide (`<header>`, `<nav>`, `<main>`, `<article>`, `<section>`, `<footer>`, `<time>`, `<fieldset>`, etc.).
- Feuilles de styles modulaires et maintenables avec variables CSS (`:root` et `[data-theme="dark"]`).
- Maîtrise du Box Model (`box-sizing: border-box`) et des mises en page modernes Flexbox / Grid.
- Conception **mobile first** et responsive sans barre de défilement horizontale à 360 px.

### Défis TP1 réalisés
1. **CSS Grid auto-fill + minmax() sans media query** :
   - Implémenté sur `.cartes` avec `grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));`.
   - **Différence avec la version Flexbox** : en Flexbox classique, il fallait obligatoirement écrire des points de rupture média (`@media (min-width: 640px)` et `@media (min-width: 960px)`) et recalculer manuellement la largeur des éléments (`flex-basis: calc(...)`). Avec CSS Grid, le navigateur calcule automatiquement le nombre optimal de colonnes selon la largeur réelle de l'écran tout en conservant une taille minimale confortable (280 px).
2. **Formulaire collant (`position: sticky`)** :
   - Sur grand écran (`≥ 960px`), le panneau de formulaire d'inscription dans `evenement.html` (`.detail-form`) reste visible lors du défilement grâce à `position: sticky; top: var(--sp-xl);`.
3. **Feuille de style d'impression (`@media print`)** :
   - Mise en page optimisée pour l'impression papier ou PDF : masquage de la navigation, du bouton de thème, des boutons favoris et du formulaire d'inscription ; typographie en noir sur fond blanc pur avec espacements nets.
4. **Accessibilité & Contrastes (WCAG AA ≥ 4.5:1)** :
   - Les ratios de contraste texte/fond ont été vérifiés via les DevTools de Chrome/Firefox (bleu `#1d4ed8` sur fond clair ≥ 4.6:1, mode sombre `#60a5fa` sur fond `#0f172a` ≥ 8.1:1).
   - Les éléments interactifs disposent d'un `:focus-visible` très visible avec outline bleu et décalage d'accentuation.

---

## 2. TP2 : ClubHub interactif (JavaScript Vanilla)

### Objectifs
- Manipulation avancée du DOM en Vanilla JS (aucun framework, aucun `onclick` inline).
- Gestion des événements utilisateur (`input`, `change`, `click`, `submit`).
- Filtrage en temps réel, tri dynamique multicritère et recherche tolérante aux accents.
- Validation côté client instantanée avec messages clairs et gestion du focus accessible.
- Persistance locale via `localStorage` (thème et favoris).

### Défis TP2 réalisés
1. **Favoris avec persistance `localStorage`** :
   - Un clic sur l'étoile (`.fav-btn`) d'une carte l'ajoute ou la retire des favoris stockés sous la clé `clubhub-favorites`.
   - La case à cocher « Seulement mes favoris » filtre dynamiquement la liste d'événements.
2. **Recherche insensible aux accents** :
   - Utilisation de `normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()` afin que la recherche « echecs » ou « robotique » trouve immédiatement « Échecs » ou « Robotique ».
3. **Résolution du flash en mode sombre (Anti-Flash)** :
   - **Problème identifié** : Lorsque le script externe `js/app.js` est exécuté à la fin du document ou via `DOMContentLoaded`, la page s'affiche d'abord avec le thème par défaut (fond blanc), puis bascule en sombre dès que le JS s'exécute. Ce saut visuel s'appelle le FOUC (*Flash of Unstyled Content*).
   - **Solution appliquée** : Un micro-script synchrone a été placé tout en haut du `<head>`. Il lit immédiatement `localStorage.getItem('clubhub-theme')` et applique `document.documentElement.dataset.theme = 'dark'` avant même le tout premier rendu graphique du navigateur. Le flash est ainsi totalement éliminé.
4. **Compteur de caractères et alerte limite** :
   - Pour le champ motivation (max 300 caractères), le compteur `#motivation-count` affiche en continu `X / 300` et active la classe `.is-near-limit` dès que 90 % de la limite (270 caractères) est atteinte.

### Erreurs de console rencontrées et résolues (F12)
1. **`Uncaught SyntaxError: Unexpected identifier 'submit'`** :
   - *Cause* : Dans la fonction `initRegistrationForm()`, des guillemets doublés `''submit''`, `''hidden''`, `''get''`, `''.is-invalid''` étaient présents au lieu de guillemets simples `'submit'`. Cela provoquait un arrêt brutal du moteur JS et une erreur rouge dans la console dès l'ouverture de la page.
   - *Correction* : Nettoyage et correction de toutes les chaînes en guillemets simples valides.
2. **Tri de dates renvoyant `NaN` (incohérence chronologique)** :
   - *Cause* : Lors du tri par date sur les attributs `data-date`, certaines cartes contenaient une chaîne au format français `14/10, 14 h` non reconnue par `new Date()`.
   - *Correction* : Mise en place de la fonction robuste `parseEventDate(val)` qui gère à la fois le format standard ISO (`2026-10-14T14:00`) et le format textuel par extraction RegExp.
3. **Empêchement de l'envoi en version statique TP2** :
   - *Cause* : Le test `method === 'get'` ne bloquait pas la soumission d'un formulaire en `method="post"`, provoquant une redirection vers `inscription.php` introuvable.
   - *Correction* : En mode statique TP2, le JS intercepte systématiquement l'envoi valide, affiche `.form-success`, réinitialise le formulaire et remet à zéro le compteur de caractères.

---

## 3. Atelier S04, TP3 & TP4 : Backend PHP, Authentification & Sécurité

### Sécurité & Bonnes pratiques
- **Échappement universel contre les failles XSS** : toute donnée injectée dans le HTML passe systématiquement par la fonction `e()` (`htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`).
- **Protection contre les injections SQL** : toutes les requêtes SQL utilisent PDO avec des requêtes préparées (`prepare()` + `execute()`).
- **Mots de passe hachés** : aucun mot de passe n'est stocké ou véhiculé en clair ; utilisation exclusive de `password_hash($pw, PASSWORD_DEFAULT)` et `password_verify()`.
- **Pattern Post/Redirect/Get (PRG)** : après chaque opération modifiant des données (connexion, déconnexion, inscription, annulation), une redirection HTTP (`302`) est déclenchée avec un message flash en session, évitant la double soumission du formulaire lors d'un rafraîchissement (F5).
- **Protection Open Redirect** : la redirection après connexion est filtrée par `safeNext()` pour interdire les URLs externes malveillantes.
- **Annulation sécurisée** : suppression d'une inscription restreinte au couple `(user_id, event_id)` de l'utilisateur connecté via méthode POST.
