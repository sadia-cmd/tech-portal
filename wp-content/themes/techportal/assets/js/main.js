/**
 * Tech Portal — Main JavaScript
 *
 * Handles: mobile navigation, search toggle, smooth interactions
 * @package TechPortal
 */

(function () {
  'use strict';

  /* ---- Mobile Navigation Toggle ---- */
  const navToggle = document.querySelector('.tp-nav-toggle');
  const navList = document.querySelector('.tp-nav__list');

  if (navToggle && navList) {
    navToggle.addEventListener('click', function () {
      const isOpen = navList.classList.toggle('tp-nav__list--open');
      navToggle.setAttribute('aria-expanded', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    // Close on escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && navList.classList.contains('tp-nav__list--open')) {
        navList.classList.remove('tp-nav__list--open');
        navToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        navToggle.focus();
      }
    });
  }

  /* ---- Smooth scroll for anchor links ---- */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* ---- Lazy loading enhancement (native + fallback) ---- */
  if ('loading' in HTMLImageElement.prototype) {
    // Native lazy loading supported
  } else {
    // Fallback: IntersectionObserver
    var lazyImages = document.querySelectorAll('img[loading="lazy"]');
    if (lazyImages.length && 'IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var img = entry.target;
            img.src = img.dataset.src || img.src;
            observer.unobserve(img);
          }
        });
      });
      lazyImages.forEach(function (img) {
        observer.observe(img);
      });
    }
  }

  /* ---- Sticky header shadow on scroll ---- */
  var header = document.querySelector('.tp-header');
  if (header) {
    var lastScroll = 0;
    window.addEventListener('scroll', function () {
      var currentScroll = window.pageYOffset;
      if (currentScroll > 10) {
        header.style.boxShadow = 'var(--tp-shadow-md)';
      } else {
        header.style.boxShadow = 'none';
      }
      lastScroll = currentScroll;
    }, { passive: true });
  }
})();
