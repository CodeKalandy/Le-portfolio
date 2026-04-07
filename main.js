/* main.js - Bernard Thiery Portfolio */

/* ════════════════════════════════════════
   CUSTOM CURSOR
   ════════════════════════════════════════ */
const cursor    = document.getElementById('cursor');
const cursorDot = document.getElementById('cursorDot');

let mx = -100, my = -100, cx = -100, cy = -100;

document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });

function animCursor() {
  cx += (mx - cx) * 0.14;
  cy += (my - cy) * 0.14;
  if (cursor) {
    cursor.style.left = cx + 'px';
    cursor.style.top  = cy + 'px';
  }
  if (cursorDot) {
    cursorDot.style.left = mx + 'px';
    cursorDot.style.top  = my + 'px';
  }
  requestAnimationFrame(animCursor);
}
animCursor();

// Délégation d'événements pour capturer les éléments injectés dynamiquement (ex: bouton theme)
document.addEventListener('mouseenter', e => {
  if (e.target.matches('a, button')) {
    if (cursor) {
      cursor.style.width       = '56px';
      cursor.style.height      = '56px';
      cursor.style.borderColor = 'var(--orange)';
      cursor.style.opacity     = '0.6';
    }
  }
}, true);

document.addEventListener('mouseleave', e => {
  if (e.target.matches('a, button')) {
    if (cursor) {
      cursor.style.width   = '36px';
      cursor.style.height  = '36px';
      cursor.style.opacity = '1';
    }
  }
}, true);


/* ════════════════════════════════════════
   NAV SCROLL
   ════════════════════════════════════════ */
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => {
  if (nav) nav.classList.toggle('scrolled', window.scrollY > 60);
});


/* ════════════════════════════════════════
   REVEAL ON SCROLL - observateur unifié
   Gère à la fois :
   - les éléments avec class="reveal" déjà dans le HTML
   - les éléments ciblés par sélecteur (sans .reveal dans le HTML)
   ════════════════════════════════════════ */

// Éléments à animer qui n'ont pas .reveal dans le HTML
const revealSelectors = [
  '.about-grid',
  '.skills-header', '.skills-cols', '.certs-band',
  '.proj-header', '.proj-grid',
  '.tl-row',
  '.contact-inner'
];

// Ajoute .reveal aux éléments ciblés s'ils ne l'ont pas déjà
revealSelectors.forEach(sel => {
  document.querySelectorAll(sel).forEach(el => {
    el.classList.add('reveal');
  });
});

// Un seul observateur pour TOUS les .reveal de la page
const io = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      io.unobserve(entry.target);
    }
  });
}, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.reveal').forEach(el => {
  io.observe(el);
});


/* ════════════════════════════════════════
   BARRE DE PROGRESSION DE LECTURE
   ════════════════════════════════════════ */
(function () {
  const bar = document.createElement('div');
  bar.id = 'read-progress';
  document.body.appendChild(bar);

  window.addEventListener('scroll', () => {
    const scrollTop    = window.scrollY;
    const docHeight    = document.documentElement.scrollHeight - window.innerHeight;
    const pct          = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
    bar.style.width    = pct + '%';
  }, { passive: true });
})();


/* ════════════════════════════════════════
   BOUTON RETOUR EN HAUT
   ════════════════════════════════════════ */
(function () {
  const btn = document.createElement('button');
  btn.id        = 'back-to-top';
  btn.innerHTML = '↑';
  btn.setAttribute('aria-label', 'Retour en haut');
  document.body.appendChild(btn);

  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();


/* ════════════════════════════════════════
   FILTRE PROJETS (page principale)
   ════════════════════════════════════════ */
(function () {
  const projGrid = document.querySelector('.proj-section-block .proj-grid');
  if (!projGrid) return;

  // Récupère tous les tags uniques depuis les cards
  const cards = Array.from(projGrid.querySelectorAll('.proj-card'));
  const allTags = new Set();
  cards.forEach(card => {
    card.querySelectorAll('.proj-tags span').forEach(t => allTags.add(t.textContent.trim()));
  });

  // Crée la barre de filtres
  const filterBar = document.createElement('div');
  filterBar.id = 'proj-filters';

  const allBtn = document.createElement('button');
  allBtn.className    = 'proj-filter-btn active';
  allBtn.textContent  = 'Tous';
  allBtn.dataset.tag  = 'all';
  filterBar.appendChild(allBtn);

  // Tags regroupés manuellement pour un affichage propre
  const featuredTags = ['Linux', 'Docker', 'MariaDB', 'PostgreSQL', 'SSL', 'Sécurité', 'Réseau', 'Self-hosted', 'PHP'];
  const tagsToShow   = featuredTags.filter(t => allTags.has(t));

  tagsToShow.forEach(tag => {
    const btn = document.createElement('button');
    btn.className   = 'proj-filter-btn';
    btn.textContent = tag;
    btn.dataset.tag = tag;
    filterBar.appendChild(btn);
  });

  // Insère avant la grille
  projGrid.parentNode.insertBefore(filterBar, projGrid);

  // Logique de filtre
  filterBar.addEventListener('click', e => {
    const btn = e.target.closest('.proj-filter-btn');
    if (!btn) return;

    filterBar.querySelectorAll('.proj-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const tag = btn.dataset.tag;
    cards.forEach(card => {
      if (tag === 'all') {
        card.style.display = '';
        return;
      }
      const cardTags = Array.from(card.querySelectorAll('.proj-tags span')).map(s => s.textContent.trim());
      card.style.display = cardTags.includes(tag) ? '' : 'none';
    });
  });
})();


/* ════════════════════════════════════════
   COPIER LES BLOCS DE CODE AU CLIC
   ════════════════════════════════════════ */
(function () {
  // Cible tous les conteneurs de code (cheat sheet et pages projets)
  document.querySelectorAll('pre, .bg-black').forEach(block => {
    // Évite les doublons
    if (block.dataset.copyable) return;
    block.dataset.copyable = '1';
    block.style.position   = 'relative';

    const btn = document.createElement('button');
    btn.className   = 'copy-code-btn';
    btn.innerHTML   = '<i class="bi bi-clipboard"></i>';
    btn.setAttribute('aria-label', 'Copier le code');
    block.appendChild(btn);

    btn.addEventListener('click', e => {
      e.stopPropagation();
      const text = block.innerText.replace(/\bCopier\b/gi, '').trim();
      navigator.clipboard.writeText(text).then(() => {
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        btn.classList.add('copied');
        setTimeout(() => {
          btn.innerHTML = '<i class="bi bi-clipboard"></i>';
          btn.classList.remove('copied');
        }, 1800);
      });
    });
  });
})();


/* ════════════════════════════════════════
   THEME NUIT / JOUR
   ════════════════════════════════════════ */
(function () {
  const STORAGE_KEY = 'bt-theme';

  // Détection de la préférence système si aucun choix sauvegardé
  function getDefaultTheme() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) return saved;
    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
  }

  // Applique immédiatement pour éviter le flash
  const initialTheme = getDefaultTheme();
  if (initialTheme === 'light') document.body.classList.add('light');

  function injectToggleButton() {
    const navRight = document.querySelector('#nav .nav-right');
    if (!navRight || document.getElementById('theme-toggle')) return;

    const btn = document.createElement('button');
    btn.id = 'theme-toggle';
    btn.setAttribute('aria-label', 'Basculer le thème');
    updateBtnLabel(btn, document.body.classList.contains('light'));
    navRight.insertBefore(btn, navRight.firstChild);
    btn.addEventListener('click', toggleTheme);
  }

  function updateBtnLabel(btn, isLight) {
    btn.innerHTML = isLight
      ? '<span class="toggle-icon">☾</span><span class="toggle-label">NUIT</span>'
      : '<span class="toggle-icon">☀</span><span class="toggle-label">JOUR</span>';
  }

  function toggleTheme() {
    const isLight = document.body.classList.toggle('light');
    localStorage.setItem(STORAGE_KEY, isLight ? 'light' : 'dark');
    const btn = document.getElementById('theme-toggle');
    if (btn) updateBtnLabel(btn, isLight);
  }

  // Écoute les changements système en temps réel (si pas de choix manuel)
  window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', e => {
    if (!localStorage.getItem(STORAGE_KEY)) {
      const isLight = e.matches;
      document.body.classList.toggle('light', isLight);
      const btn = document.getElementById('theme-toggle');
      if (btn) updateBtnLabel(btn, isLight);
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', injectToggleButton);
  } else {
    injectToggleButton();
  }
})();