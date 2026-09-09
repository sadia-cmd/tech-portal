/**
 * Tech Portal — Main JavaScript
 *
 * Handles: mobile navigation overlay, search, newsletter AJAX, smooth interactions
 * @package TechPortal
 */

(function () {
  /* ---- Clear stale dark mode from localStorage ---- */
  try { localStorage.removeItem('tp_dark_mode'); } catch(e) {}
  document.documentElement.classList.remove('dark');
  document.body.classList.remove('dark');
  'use strict';

  /* ---- Mobile Navigation Overlay ---- */
  var navToggle = document.querySelector('.tp-nav-toggle');
  var mobileOverlay = document.getElementById('tp-mobile-nav');
  var mobileClose = document.querySelector('.tp-nav__mobile-close');

  function openMobileNav() {
    if (!mobileOverlay) return;
    mobileOverlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    if (navToggle) navToggle.setAttribute('aria-expanded', 'true');
    // Focus first link
    var firstLink = mobileOverlay.querySelector('a');
    if (firstLink) setTimeout(function() { firstLink.focus(); }, 100);
  }

  function closeMobileNav() {
    if (!mobileOverlay) return;
    mobileOverlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (navToggle) {
      navToggle.setAttribute('aria-expanded', 'false');
      navToggle.focus();
    }
  }

  if (navToggle && mobileOverlay) {
    navToggle.addEventListener('click', function () {
      var isOpen = mobileOverlay.getAttribute('aria-hidden') === 'false';
      if (isOpen) {
        closeMobileNav();
      } else {
        openMobileNav();
      }
    });
  }

  if (mobileClose) {
    mobileClose.addEventListener('click', closeMobileNav);
  }

  // Close on escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && mobileOverlay && mobileOverlay.getAttribute('aria-hidden') === 'false') {
      closeMobileNav();
    }
  });

  // Close mobile nav on link click
  if (mobileOverlay) {
    mobileOverlay.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        closeMobileNav();
      });
    });
  }

  /* ---- Desktop nav list toggle (fallback for no overlay) ---- */
  var navList = document.querySelector('.tp-nav__list');
  if (navToggle && navList && !mobileOverlay) {
    navToggle.addEventListener('click', function () {
      var isOpen = navList.classList.toggle('tp-nav__list--open');
      navToggle.setAttribute('aria-expanded', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });
  }

  /* ---- Search button → navigate to search ---- */
  var searchBtns = document.querySelectorAll('button[aria-label="Search"], .tp-btn--search');
  searchBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      window.location.href = '/?s=';
    });
  });

  /* ---- Newsletter Form AJAX ---- */
  var newsletterForms = document.querySelectorAll('[data-newsletter]');
  newsletterForms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var emailInput = form.querySelector('input[type="email"]');
      var submitBtn = form.querySelector('button[type="submit"]');
      if (!emailInput || !submitBtn) return;

      var email = emailInput.value.trim();
      if (!email) return;

      var originalText = submitBtn.textContent;
      submitBtn.textContent = '...';
      submitBtn.disabled = true;

      var data = new FormData();
      data.append('action', 'tp_newsletter_subscribe');
      data.append('email', email);
      data.append('nonce', techportal.nonce);

      fetch(techportal.ajaxurl, {
        method: 'POST',
        body: data,
        credentials: 'same-origin'
      })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        if (result.success) {
          submitBtn.textContent = '✓ Subscribed';
          emailInput.value = '';
          emailInput.disabled = true;
          setTimeout(function () {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            emailInput.disabled = false;
          }, 3000);
        } else {
          submitBtn.textContent = result.data?.message || 'Error';
          setTimeout(function () {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
          }, 3000);
        }
      })
      .catch(function () {
        submitBtn.textContent = 'Error';
        setTimeout(function () {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }, 3000);
      });
    });
  });

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

  /* ---- Sticky header shadow on scroll ---- */
  var header = document.querySelector('.tp-header');
  if (header) {
    window.addEventListener('scroll', function () {
      if (window.pageYOffset > 10) {
        header.style.boxShadow = 'var(--tp-shadow-md)';
      } else {
        header.style.boxShadow = 'none';
      }
    }, { passive: true });
  }

  /* ---- Cookie Consent ---- */
  var COOKIE_CONSENT_KEY = 'tp_cookie_consent';
  var cookieBanner = document.getElementById('tp-cookie-banner');
  if (cookieBanner) {
    var storedConsent = null;
    try {
      storedConsent = localStorage.getItem(COOKIE_CONSENT_KEY);
    } catch (e) {
      storedConsent = null;
    }
    if (!storedConsent) {
      cookieBanner.hidden = false;
    }

    function setCookieConsent(value) {
      try {
        localStorage.setItem(COOKIE_CONSENT_KEY, value);
      } catch (e) {
        // localStorage unavailable (private mode, etc.) — banner simply won't persist
      }
      cookieBanner.hidden = true;
    }

    var cookieAccept = cookieBanner.querySelector('.tp-cookie-banner__accept');
    var cookieDecline = cookieBanner.querySelector('.tp-cookie-banner__decline');
    if (cookieAccept) {
      cookieAccept.addEventListener('click', function () { setCookieConsent('accepted'); });
    }
    if (cookieDecline) {
      cookieDecline.addEventListener('click', function () { setCookieConsent('declined'); });
    }
  }

  /* ---- Lazy loading fallback ---- */
  if (!('loading' in HTMLImageElement.prototype)) {
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

})();
