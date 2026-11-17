/**
 * ClubHub — js/app.js
 * TP2 : ClubHub interactif
 * Auteurs : binôme
 *
 * Règles respectées :
 * - Pure Vanilla JS (aucun jQuery ni bibliothèque).
 * - Tout le code est dans ce fichier, aucun attribut onclick dans le HTML.
 * - Utilisation exclusive de const et let, jamais de var.
 * - Comparaisons strictes (=== et !==).
 * - Utilisation de textContent (jamais d'innerHTML avec des données saisies) pour éviter les failles XSS.
 * - Aucune erreur dans la console (F12) sur aucune page (présence de vérifications conditionnelles).
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  /* ============================================================
     1. GESTION DU THÈME (MODE SOMBRE / CLAIR) & ANTI-FLASH
     ============================================================ */
  initThemeToggle();

  /* ============================================================
     2. GESTION DES FAVORIS (DÉFI LOCALSTORAGE)
     ============================================================ */
  initFavorites();

  /* ============================================================
     3. PAGE ÉVÉNEMENTS : RECHERCHE, FILTRE, TRI & COMPTEUR
     ============================================================ */
  initEventsPage();

  /* ============================================================
     4. PAGE DÉTAIL ÉVÉNEMENT : FORMULAIRE D'INSCRIPTION & COMPTEUR
     ============================================================ */
  initRegistrationForm();
});

/* ============================================================
   1. THÈME SOMBRE / CLAIR (Point 8)
   ============================================================ */
function initThemeToggle() {
  const toggleButtons = document.querySelectorAll('.theme-toggle');
  if (toggleButtons.length === 0) return;

  const THEME_KEY = 'clubhub-theme';

  // Fonction de mise à jour du texte des boutons
  const updateButtonText = (isDark) => {
    toggleButtons.forEach((btn) => {
      btn.textContent = isDark ? 'Mode clair' : 'Mode sombre';
    });
  };

  // Lecture du thème actuel déjà posé par le script anti-flash ou le DOM
  const currentTheme = document.documentElement.dataset.theme;
  const isDark = currentTheme === 'dark';
  updateButtonText(isDark);

  toggleButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const nowDark = document.documentElement.dataset.theme === 'dark';
      if (nowDark) {
        delete document.documentElement.dataset.theme;
        localStorage.setItem(THEME_KEY, 'light');
        updateButtonText(false);
      } else {
        document.documentElement.dataset.theme = 'dark';
        localStorage.setItem(THEME_KEY, 'dark');
        updateButtonText(true);
      }
    });
  });
}

/* ============================================================
   2. FAVORIS (Défi LocalStorage)
   ============================================================ */
const FAVORITES_KEY = 'clubhub-favorites';

function getStoredFavorites() {
  try {
    const raw = localStorage.getItem(FAVORITES_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function saveFavorites(favs) {
  try {
    localStorage.setItem(FAVORITES_KEY, JSON.stringify(favs));
  } catch (err) {
    console.error('Erreur lors de la sauvegarde des favoris :', err);
  }
}

function initFavorites() {
  const favButtons = document.querySelectorAll('.fav-btn');
  if (favButtons.length === 0) return;

  const favorites = new Set(getStoredFavorites());

  // Mise à jour de l'affichage initial des boutons étoiles
  favButtons.forEach((btn) => {
    const cardId = btn.dataset.favId;
    if (!cardId) return;

    const isFav = favorites.has(cardId);
    btn.classList.toggle('is-fav', isFav);
    btn.setAttribute('aria-label', isFav ? 'Retirer des favoris' : 'Ajouter aux favoris');
    btn.setAttribute('aria-pressed', isFav ? 'true' : 'false');

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const currentFavs = new Set(getStoredFavorites());
      const nowFav = !currentFavs.has(cardId);

      if (nowFav) {
        currentFavs.add(cardId);
      } else {
        currentFavs.delete(cardId);
      }

      saveFavorites([...currentFavs]);

      // Mettre à jour tous les boutons avec cet ID (ex: si présent sur plusieurs pages)
      const relatedButtons = document.querySelectorAll(`.fav-btn[data-fav-id="${cardId}"]`);
      relatedButtons.forEach((b) => {
        b.classList.toggle('is-fav', nowFav);
        b.setAttribute('aria-label', nowFav ? 'Retirer des favoris' : 'Ajouter aux favoris');
        b.setAttribute('aria-pressed', nowFav ? 'true' : 'false');
      });

      // Si le filtre "Favoris seulement" est actif sur evenements.html, recalculer
      const favFilter = document.querySelector('#fav-filter');
      if (favFilter && favFilter.checked) {
        window.dispatchEvent(new CustomEvent('clubhub:filter-changed'));
      }
    });
  });
}

/* ============================================================
   3. PAGE ÉVÉNEMENTS (Points 1, 2, 3, 9 & Défis Accents/Favoris)
   ============================================================ */
function initEventsPage() {
  const filtersForm = document.querySelector('.filters');
  const cardsContainer = document.querySelector('.cartes');
  if (!filtersForm || !cardsContainer) return;

  const searchInput = document.querySelector('#search');
  const clubFilter = document.querySelector('#club-filter');
  const sortSelect = document.querySelector('#sort');
  const favFilter = document.querySelector('#fav-filter');
  const resultsCount = document.querySelector('.results-count');
  const emptyMessage = document.querySelector('.empty-message');

  // Sélection de toutes les cartes d'événements
  const cardElements = Array.from(cardsContainer.querySelectorAll('article'));
  if (cardElements.length === 0) return;

  // Sauvegarde de l'ordre d'origine pour le tri "default"
  const originalOrder = [...cardElements];

  /**
   * Défi Accents : normalise une chaîne en supprimant diacritiques et majuscules
   * Exemple : "Échecs" -> "echecs"
   */
  const normalizeText = (text) => {
    return (text || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim();
  };

  // Fonction principale de filtrage et tri
  const applyFiltersAndSort = () => {
    const query = searchInput ? normalizeText(searchInput.value) : '';
    const selectedClub = clubFilter ? clubFilter.value : '';
    const onlyFavs = favFilter ? favFilter.checked : false;
    const currentFavs = new Set(getStoredFavorites());

    let visibleCount = 0;

    // 1. Filtrage des cartes
    cardElements.forEach((card) => {
      const cardClub = card.dataset.club || '';
      const cardId = card.dataset.id || '';
      // On extrait tout le texte de la carte normalisé pour la recherche
      const cardText = normalizeText(card.textContent);

      const matchesSearch = query === '' || cardText.includes(query);
      const matchesClub = selectedClub === '' || cardClub === selectedClub;
      const matchesFav = !onlyFavs || currentFavs.has(cardId);

      const isVisible = matchesSearch && matchesClub && matchesFav;

      if (isVisible) {
        card.style.display = '';
        visibleCount += 1;
      } else {
        card.style.display = 'none';
      }
    });

    // 2. Tri des cartes dans le DOM
    const sortMode = sortSelect ? sortSelect.value : 'default';
    const sortedCards = [...cardElements];

    if (sortMode === 'places-desc') {
      // Du plus grand au plus petit nombre de places
      sortedCards.sort((a, b) => {
        const placesA = parseInt(a.dataset.places, 10) || 0;
        const placesB = parseInt(b.dataset.places, 10) || 0;
        return placesB - placesA;
      });
    } else if (sortMode === 'places-asc') {
      // Du plus petit au plus grand
      sortedCards.sort((a, b) => {
        const placesA = parseInt(a.dataset.places, 10) || 0;
        const placesB = parseInt(b.dataset.places, 10) || 0;
        return placesA - placesB;
      });
    } else if (sortMode === 'date-asc') {
      // Date la plus proche d'abord
      sortedCards.sort((a, b) => {
        const dateA = new Date(a.dataset.date || 0);
        const dateB = new Date(b.dataset.date || 0);
        return dateA - dateB;
      });
    } else if (sortMode === 'date-desc') {
      // Date la plus lointaine d'abord
      sortedCards.sort((a, b) => {
        const dateA = new Date(a.dataset.date || 0);
        const dateB = new Date(b.dataset.date || 0);
        return dateB - dateA;
      });
    } else {
      // Ordre par défaut initial
      sortedCards.sort((a, b) => originalOrder.indexOf(a) - originalOrder.indexOf(b));
    }

    // Réinsertion ordonnée dans le conteneur
    sortedCards.forEach((card) => {
      cardsContainer.appendChild(card);
    });

    // 3. Mise à jour du compteur de résultats (Point 3)
    if (resultsCount) {
      if (visibleCount === 1) {
        resultsCount.textContent = '1 événement affiché';
      } else {
        resultsCount.textContent = `${visibleCount} événements affichés`;
      }
    }

    // 4. Affichage ou masquage du message vide
    if (emptyMessage) {
      if (visibleCount === 0) {
        emptyMessage.removeAttribute('hidden');
      } else {
        emptyMessage.setAttribute('hidden', '');
      }
    }
  };

  // Écouteurs sur les champs de filtrage
  if (searchInput) {
    searchInput.addEventListener('input', applyFiltersAndSort);
  }
  if (clubFilter) {
    clubFilter.addEventListener('change', applyFiltersAndSort);
  }
  if (sortSelect) {
    sortSelect.addEventListener('change', applyFiltersAndSort);
  }
  if (favFilter) {
    favFilter.addEventListener('change', applyFiltersAndSort);
  }

  // Écoute d'un événement global si un favori a été cliqué
  window.addEventListener('clubhub:filter-changed', applyFiltersAndSort);

  // Exécution initiale
  applyFiltersAndSort();
}

/* ============================================================
   4. FORMULAIRE D'INSCRIPTION & VALIDATION (Points 4, 5, 6, 7, 10)
   ============================================================ */
function initRegistrationForm() {
  const form = document.querySelector('#registration-form');
  if (!form) return;

  const nomInput = form.querySelector('#nom');
  const emailInput = form.querySelector('#email');
  const niveauSelect = form.querySelector('#niveau');
  const reglementCheckbox = form.querySelector('#reglement');
  const motivationTextarea = form.querySelector('#motivation');
  const motivationCount = form.querySelector('#motivation-count');
  const formSuccess = form.querySelector('.form-success');

  const MOTIVATION_LIMIT = 300;

  // --- Point 7 : Compteur de caractères motivation ---
  if (motivationTextarea && motivationCount) {
    const updateMotivationCount = () => {
      const length = motivationTextarea.value.length;
      motivationCount.textContent = `${length} / ${MOTIVATION_LIMIT}`;

      // À partir de 90% (270 caractères), ajouter la classe is-near-limit
      const isNear = length >= MOTIVATION_LIMIT * 0.9;
      motivationCount.classList.toggle('is-near-limit', isNear);
    };

    motivationTextarea.addEventListener('input', updateMotivationCount);
    updateMotivationCount();
  }

  // Fonctions utilitaires d'affichage d'erreurs
  const setError = (inputElement, errorElementId, message) => {
    const errElem = document.querySelector(`#${errorElementId}`);
    if (errElem) {
      errElem.textContent = message;
    }
    if (inputElement) {
      inputElement.classList.add('is-invalid');
      inputElement.setAttribute('aria-invalid', 'true');
    }
  };

  const clearError = (inputElement, errorElementId) => {
    const errElem = document.querySelector(`#${errorElementId}`);
    if (errElem) {
      errElem.textContent = '';
    }
    if (inputElement) {
      inputElement.classList.remove('is-invalid');
      inputElement.removeAttribute('aria-invalid');
    }
  };

  // Règles de validation spécifiques pour chaque champ
  const validateNom = () => {
    if (!nomInput) return true;
    const value = nomInput.value.trim();
    if (value.length < 3) {
      setError(nomInput, 'nom-error', 'Le nom doit comporter au moins 3 caractères.');
      return false;
    }
    clearError(nomInput, 'nom-error');
    return true;
  };

  const validateEmail = () => {
    if (!emailInput) return true;
    const value = emailInput.value.trim();
    // Regex de validation d'email simple et standard
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (value === '') {
      setError(emailInput, 'email-error', "L'adresse email est requise.");
      return false;
    }
    if (!emailRegex.test(value)) {
      setError(emailInput, 'email-error', 'Veuillez saisir une adresse email valide.');
      return false;
    }
    clearError(emailInput, 'email-error');
    return true;
  };

  const validateNiveau = () => {
    if (!niveauSelect) return true;
    if (niveauSelect.value === '') {
      setError(niveauSelect, 'niveau-error', "Veuillez sélectionner un niveau d'études.");
      return false;
    }
    clearError(niveauSelect, 'niveau-error');
    return true;
  };

  const validateReglement = () => {
    if (!reglementCheckbox) return true;
    if (!reglementCheckbox.checked) {
      setError(reglementCheckbox, 'reglement-error', 'Vous devez accepter le règlement.');
      return false;
    }
    clearError(reglementCheckbox, 'reglement-error');
    return true;
  };

  // --- Point 5 : Correction en direct pendant la saisie ---
  if (nomInput) {
    nomInput.addEventListener('input', () => {
      if (nomInput.classList.contains('is-invalid')) {
        validateNom();
      }
    });
  }

  if (emailInput) {
    emailInput.addEventListener('input', () => {
      if (emailInput.classList.contains('is-invalid')) {
        validateEmail();
      }
    });
  }

  if (niveauSelect) {
    niveauSelect.addEventListener('change', () => {
      if (niveauSelect.classList.contains('is-invalid')) {
        validateNiveau();
      }
    });
  }

  if (reglementCheckbox) {
    reglementCheckbox.addEventListener('change', () => {
      if (reglementCheckbox.classList.contains('is-invalid')) {
        validateReglement();
      }
    });
  }

  // --- Points 4, 6 & 10 : Validation à l''envoi et accessibilité ---
  form.addEventListener(''submit'', (e) => {
    if (formSuccess) {
      formSuccess.setAttribute(''hidden'', '''');
    }

    const isNomValid = validateNom();
    const isEmailValid = validateEmail();
    const isNiveauValid = validateNiveau();
    const isReglementValid = validateReglement();

    if (!(isNomValid && isEmailValid && isNiveauValid && isReglementValid)) {
      e.preventDefault();
      const firstInvalidField = form.querySelector(''.is-invalid'');
      if (firstInvalidField) {
        firstInvalidField.focus();
      }
      return;
    }

    const method = (form.getAttribute(''method'') || ''get'').toLowerCase();
    if (method === ''get'') {
      e.preventDefault();
      if (formSuccess) {
        formSuccess.removeAttribute(''hidden'');
      }
      form.reset();
      if (motivationCount) {
        motivationCount.textContent = `0 / ${MOTIVATION_LIMIT}`;
        motivationCount.classList.remove(''is-near-limit'');
      }
    }
  });
}