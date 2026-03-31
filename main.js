/* main.js - Bernard Thiery Portfolio v2 */

/* ---- CUSTOM CURSOR ---- */
const cursor    = document.getElementById('cursor');
const cursorDot = document.getElementById('cursorDot');

let mx = -100, my = -100, cx = -100, cy = -100;

document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });

function animCursor() {
  cx += (mx - cx) * 0.14;
  cy += (my - cy) * 0.14;
  cursor.style.left    = cx + 'px';
  cursor.style.top     = cy + 'px';
  cursorDot.style.left = mx + 'px';
  cursorDot.style.top  = my + 'px';
  requestAnimationFrame(animCursor);
}
animCursor();

document.querySelectorAll('a, button').forEach(el => {
  el.addEventListener('mouseenter', () => {
    cursor.style.width  = '56px';
    cursor.style.height = '56px';
    cursor.style.borderColor = 'var(--orange)';
    cursor.style.opacity = '0.6';
  });
  el.addEventListener('mouseleave', () => {
    cursor.style.width  = '36px';
    cursor.style.height = '36px';
    cursor.style.opacity = '1';
  });
});

/* ---- NAV SCROLL ---- */
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 60);
});

/* ---- REVEAL ON SCROLL ---- */
const reveals = document.querySelectorAll(
  '.about-grid, .skills-header, .skills-cols, .certs-band, .proj-header, .proj-grid, .tl-row, .contact-inner'
);

const io = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('reveal', 'visible');
      io.unobserve(entry.target);
    }
  });
}, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

reveals.forEach(el => {
  el.classList.add('reveal');
  io.observe(el);
});
