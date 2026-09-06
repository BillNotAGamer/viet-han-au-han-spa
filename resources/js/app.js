import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

import.meta.glob([
    '../images/**',
    '!../images/images resource/**',
], { eager: true });

// Site-wide Premium Reversible Motion Engine
(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        return;
    }

    const initMotion = () => {
        document.documentElement.classList.add('motion-ready');

        // Setup auto-stagger delays for groups first
        const revealGroups = document.querySelectorAll('[data-reveal-group]');
        revealGroups.forEach(group => {
            const items = group.querySelectorAll('[data-reveal]');
            items.forEach((item, idx) => {
                if (!item.hasAttribute('data-reveal-delay')) {
                    item.setAttribute('data-reveal-delay', (idx * 80).toString());
                }
            });
        });

        // Reversible Scroll Reveal Elements
        const revealElements = document.querySelectorAll('[data-reveal], [data-reveal-image]');
        if (!revealElements.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            revealElements.forEach(el => {
                el.classList.add('is-visible', 'is-revealed');
            });
            return;
        }

        // Synchronous initial placement:
        // Elements currently in the viewport (hero at top, or mid-page upon refresh)
        // become instantly visible without any blank flash.
        const windowHeight = window.innerHeight;
        revealElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < windowHeight && rect.bottom > 0) {
                el.classList.add('is-visible', 'is-revealed');
            } else if (rect.top < 0) {
                el.classList.add('is-exited-above');
            } else {
                el.classList.add('is-exited-below');
            }
        });

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -4% 0px',
            threshold: 0.05,
        };

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const target = entry.target;
                if (entry.isIntersecting) {
                    // Re-entering viewport: fade in
                    const delay = target.getAttribute('data-reveal-delay');
                    if (delay && !isNaN(parseInt(delay, 10))) {
                        target.style.transitionDelay = `${delay}ms`;
                    } else {
                        target.style.transitionDelay = '0ms';
                    }

                    target.classList.add('is-visible', 'is-revealed');
                    target.classList.remove('is-exited-above', 'is-exited-below');
                } else {
                    // Leaving viewport: fade out in scroll direction
                    target.style.transitionDelay = '0ms';
                    target.classList.remove('is-visible', 'is-revealed');

                    if (entry.boundingClientRect.top < 0) {
                        target.classList.add('is-exited-above');
                        target.classList.remove('is-exited-below');
                    } else {
                        target.classList.add('is-exited-below');
                        target.classList.remove('is-exited-above');
                    }
                }
            });
        }, observerOptions);

        revealElements.forEach(el => {
            revealObserver.observe(el);
        });

        // Subtle Parallax Engine for [data-parallax] (Desktop only, clamped to 18px max)
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        if (parallaxElements.length && window.innerWidth >= 1024) {
            let ticking = false;

            const updateParallax = () => {
                const h = window.innerHeight;

                parallaxElements.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    if (rect.top < h && rect.bottom > 0) {
                        const speed = parseFloat(el.getAttribute('data-parallax-speed') || '0.04');
                        const centerY = rect.top + rect.height / 2;
                        const offset = (centerY - h / 2) * speed;
                        const clampedOffset = Math.max(-18, Math.min(18, offset));
                        el.style.setProperty('--parallax-offset', `${clampedOffset.toFixed(1)}px`);
                    }
                });

                ticking = false;
            };

            window.addEventListener('scroll', () => {
                if (!ticking) {
                    window.requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }, { passive: true });

            updateParallax();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMotion);
    } else {
        initMotion();
    }
})();
