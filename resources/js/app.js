import Swiper from 'swiper/bundle';
import GLightbox from 'glightbox';
import Swal from 'sweetalert2';
import AOS from 'aos';
import './popup.js';

if (typeof window !== 'undefined') {
  window.Swiper = Swiper;
  window.GLightbox = GLightbox;
  window.Swal = Swal;
  window.AOS = AOS;
}

(() => {
  'use strict';

  const ready = callback => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
      return;
    }

    callback();
  };

  ready(() => {
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
      const input = document.getElementById(button.dataset.passwordToggle);
      const label = button.querySelector('.sr-only');
      const icon = button.querySelector('i');

      if (!input) return;

      button.addEventListener('click', () => {
        const isVisible = input.type === 'text';

        input.type = isVisible ? 'password' : 'text';
        button.setAttribute('aria-pressed', String(!isVisible));
        button.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
        label.textContent = isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu';
        icon?.classList.toggle('fa-eye', isVisible);
        icon?.classList.toggle('fa-eye-slash', !isVisible);
      });
    });

    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileMenuPanel = mobileMenu?.querySelector('[data-mobile-menu-panel]');
    const mobileMenuBackdrop = mobileMenu?.querySelector('[data-mobile-menu-backdrop]');
    const setMobileMenu = isOpen => {
      if (!mobileMenu || !mobileMenuPanel) return;

      mobileMenu.classList.toggle('is-open', isOpen);
      mobileMenuPanel.setAttribute('aria-hidden', String(!isOpen));
      document.body.classList.toggle('overflow-hidden', isOpen);
    };

    document.querySelectorAll('[data-mobile-menu-open]').forEach(button => button.addEventListener('click', () => setMobileMenu(true)));
    document.querySelectorAll('[data-mobile-menu-close]').forEach(button => button.addEventListener('click', () => setMobileMenu(false)));
    mobileMenuBackdrop?.addEventListener('click', () => setMobileMenu(false));
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') setMobileMenu(false);
    });

    const eventRegistrationModal = document.querySelector('[data-event-registration-modal]');
    const eventRegistrationForm = eventRegistrationModal?.querySelector('[data-event-registration-form]');
    const eventRegistrationName = eventRegistrationModal?.querySelector('[data-event-registration-name]');
    const setEventRegistrationModal = isOpen => {
      if (!eventRegistrationModal) return;

      eventRegistrationModal.classList.toggle('hidden', !isOpen);
      eventRegistrationModal.classList.toggle('is-open', isOpen);
      eventRegistrationModal.setAttribute('aria-hidden', String(!isOpen));
      document.body.classList.toggle('overflow-hidden', isOpen);
    };

    document.querySelectorAll('[data-event-register-open]').forEach(button => {
      button.addEventListener('click', () => {
        if (!eventRegistrationForm) return;

        eventRegistrationForm.action = eventRegistrationForm.dataset.eventRegistrationAction
          ?.replace('__event__', encodeURIComponent(button.dataset.eventSlug || '')) || '';
        if (eventRegistrationName) eventRegistrationName.textContent = button.dataset.eventName || '';
        setEventRegistrationModal(true);
        eventRegistrationForm.querySelector('[name="full_name"]')?.focus();
      });
    });

    eventRegistrationModal?.querySelectorAll('[data-event-register-close]').forEach(button => {
      button.addEventListener('click', () => setEventRegistrationModal(false));
    });

    document.querySelectorAll('[data-mobile-collapse-toggle]').forEach(button => {
      button.addEventListener('click', () => {
        const target = document.getElementById(button.dataset.mobileCollapseToggle);
        const isOpen = target?.classList.toggle('is-open') ?? false;
        button.setAttribute('aria-expanded', String(isOpen));
      });
    });

    document.querySelectorAll('[data-faq-toggle]').forEach(button => {
      button.addEventListener('click', () => {
        const target = document.getElementById(button.dataset.faqToggle);
        const isOpen = target?.classList.toggle('is-open') ?? false;
        button.setAttribute('aria-expanded', String(isOpen));
        button.classList.toggle('is-open', isOpen);
      });
    });

    document.querySelectorAll('[data-language-tab-toggle]').forEach(button => {
      button.addEventListener('click', () => {
        const root = button.closest('[data-language-tabs]')?.parentElement;
        const targetId = button.dataset.languageTabToggle;
        const target = root?.querySelector(`#${CSS.escape(targetId)}`);

        root?.querySelectorAll('[data-language-tab-toggle]').forEach(tab => {
          const isActive = tab === button;
          tab.classList.toggle('border-primary', isActive);
          tab.classList.toggle('bg-primary-soft', isActive);
          tab.classList.toggle('text-primary', isActive);
          tab.classList.toggle('bg-white', !isActive);
          tab.setAttribute('aria-selected', String(isActive));
        });
        root?.querySelectorAll('[data-language-tab-panel]').forEach(panel => panel.classList.add('hidden'));
        target?.classList.remove('hidden');
      });
    });

    const setModal = (modal, isOpen) => {
      if (!modal) return;

      modal.classList.toggle('hidden', !isOpen);
      modal.classList.toggle('is-open', isOpen);
      modal.setAttribute('aria-hidden', String(!isOpen));
      document.body.classList.toggle('overflow-hidden', isOpen);
    };

    document.querySelectorAll('[data-ui-modal]').forEach(modal => {
      modal.querySelectorAll('[data-ui-modal-close]').forEach(button => {
        button.addEventListener('click', () => setModal(modal, false));
      });
    });

    const hero = document.querySelector('[data-home-hero-swiper]');
    if (hero && window.Swiper) {
      new window.Swiper(hero, {
        loop: Number(hero.dataset.slideCount || 0) > 1,
        autoHeight: true,
        speed: 700,
        autoplay: Number(hero.dataset.slideCount || 0) > 1 ? { delay: 6000, disableOnInteraction: false } : false,
        touchStartPreventDefault: false,
        preventClicks: false,
        preventClicksPropagation: false,
        pagination: { el: hero.querySelector('.swiper-pagination'), clickable: true },
        navigation: {
          prevEl: hero.querySelector('.home-hero-prev'),
          nextEl: hero.querySelector('.home-hero-next'),
        },
      });
    }

    const testimonials = document.querySelector('[data-home-testimonials-swiper]');
    if (testimonials && window.Swiper) {
      new window.Swiper(testimonials, {
        slidesPerView: 1,
        spaceBetween: 24,
        pagination: { el: testimonials.querySelector('.home-testimonials-pagination'), clickable: true },
        breakpoints: {
          768: { slidesPerView: 2 },
          1200: { slidesPerView: 3 },
        },
      });
    }

    const projectViews = [...document.querySelectorAll('[data-project-view]')];
    const projectSwipers = new Map();

    const initProjectSwiper = view => {
      if (!view || projectSwipers.has(view) || !window.Swiper) return;

      const swiperElement = view.querySelector('[data-project-swiper]');
      if (!swiperElement) return;

      const swiper = new window.Swiper(swiperElement, {
        slidesPerView: 1.08,
        spaceBetween: 16,
        speed: 650,
        grabCursor: true,
        watchOverflow: true,
        pagination: {
          el: view.querySelector('[data-project-progress]'),
          type: 'progressbar',
        },
        navigation: {
          prevEl: view.querySelector('[data-project-prev]'),
          nextEl: view.querySelector('[data-project-next]'),
        },
        breakpoints: {
          576: { slidesPerView: 1.35 },
          768: { slidesPerView: 2 },
          1200: { slidesPerView: 3 },
        },
      });

      projectSwipers.set(view, swiper);
    };

    projectViews.filter(view => view.classList.contains('is-active')).forEach(initProjectSwiper);

    document.querySelectorAll('[data-project-filter]').forEach(button => {
      button.addEventListener('click', () => {
        const filter = button.dataset.projectFilter;

        document.querySelectorAll('[data-project-filter]').forEach(item => {
          const isActive = item === button;
          item.classList.toggle('is-active', isActive);
          item.setAttribute('aria-selected', String(isActive));
        });

        projectViews.forEach(view => {
          const isActive = view.dataset.projectView === filter;
          view.classList.toggle('is-active', isActive);

          if (isActive) {
            initProjectSwiper(view);
            projectSwipers.get(view)?.update();
          }
        });
      });
    });

    if (window.GLightbox) {
      window.GLightbox({ selector: '.glightbox' });
    }

    const siteHeader = document.querySelector('[data-site-header]');
    const desktopHeader = window.matchMedia('(min-width: 992px)');
    let previousScrollY = window.scrollY;

    const updateHeader = () => {
      if (!siteHeader) return;

      const currentScrollY = window.scrollY;
      siteHeader.classList.toggle('is-scrolled', currentScrollY > 8);

      if (!desktopHeader.matches || currentScrollY <= 90 || currentScrollY < previousScrollY) {
        siteHeader.classList.remove('is-hidden');
      } else if (currentScrollY > previousScrollY && currentScrollY > 90) {
        siteHeader.classList.add('is-hidden');
      }

      previousScrollY = currentScrollY;
    };

    window.addEventListener('scroll', updateHeader, { passive: true });
    updateHeader();

    const backToTop = document.querySelector('[data-back-to-top]');
    const progressCircle = backToTop?.querySelector('[data-back-to-top-progress]');

    if (backToTop) {
      const circumference = 2 * Math.PI * 20;
      progressCircle?.style.setProperty('stroke-dasharray', String(circumference));

      const updateScrollProgress = () => {
        const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = scrollableHeight > 0 ? Math.min(1, Math.max(0, window.scrollY / scrollableHeight)) : 0;
        const percentage = Math.round(progress * 100);

        progressCircle?.style.setProperty('stroke-dashoffset', String(circumference * (1 - progress)));
        backToTop.classList.toggle('is-visible', window.scrollY > 280);
        backToTop.setAttribute('aria-label', `Lên đầu trang · Đã đọc ${percentage}%`);
      };

      window.addEventListener('scroll', updateScrollProgress, { passive: true });
      window.addEventListener('resize', updateScrollProgress, { passive: true });
      updateScrollProgress();
      backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      });
    }
  });
})();
