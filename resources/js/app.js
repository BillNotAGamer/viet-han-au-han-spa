import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

import.meta.glob([
    '../images/**',
    '!../images/images resource/**',
], { eager: true });

// Public V2 Motion Coordinator
// Progressive enhancement only: content remains visible until this runs.
(() => {
    const initMotion = () => {
        if (!document.body?.classList.contains('v2-public-shell')) {
            return;
        }

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            return;
        }

        document.documentElement.classList.add('motion-ready');

        // Native cross-document transitions remain browser-owned for public
        // navigation. Form submissions deliberately retain their normal PRG
        // behavior without a transition snapshot or interception.
        document.addEventListener('submit', () => {
            document.documentElement.classList.add('v2-skip-next-transition');
            window.setTimeout(() => {
                document.documentElement.classList.remove('v2-skip-next-transition');
            }, 1000);
        }, { capture: true });

        // Small editorial groups receive a restrained stagger. Longer lists
        // converge quickly rather than becoming a cascading animation.
        const revealGroups = document.querySelectorAll('[data-reveal-group]');
        revealGroups.forEach(group => {
            const items = group.querySelectorAll('[data-reveal]');
            items.forEach((item, idx) => {
                if (!item.hasAttribute('data-reveal-delay')) {
                    item.style.setProperty('--v2-reveal-delay', `${Math.min(idx, 2) * 80}ms`);
                }
            });
        });

        // Reveal compositions once on entry. They are deliberately never
        // hidden again when a visitor scrolls back through the page.
        const revealElements = document.querySelectorAll('[data-reveal], [data-reveal-image]');
        const reveal = element => {
            element.classList.add('is-revealed');
        };

        if ('IntersectionObserver' in window && revealElements.length) {
            const revealObserver = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        reveal(entry.target);
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '0px 0px -8% 0px',
                threshold: 0.08,
            });

            revealElements.forEach(element => {
                const rect = element.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    reveal(element);
                } else {
                    revealObserver.observe(element);
                }
            });
        } else {
            revealElements.forEach(reveal);
        }

        const quietHero = document.querySelector('[data-persistent-quiet-hero]');
        const persistentControls = document.querySelectorAll('[data-persistent-ui]');
        const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

        const setPersistentQuiet = isQuiet => {
            persistentControls.forEach(control => {
                control.classList.toggle('is-quiet', isQuiet);
                control.toggleAttribute('aria-hidden', isQuiet);

                if ('inert' in control) {
                    control.inert = isQuiet;
                }

                control.querySelectorAll(focusableSelector).forEach(focusable => {
                    if (isQuiet) {
                        focusable.dataset.v2MotionTabindex = focusable.getAttribute('tabindex') ?? '';
                        focusable.setAttribute('tabindex', '-1');
                    } else if ('v2MotionTabindex' in focusable.dataset) {
                        const previousTabindex = focusable.dataset.v2MotionTabindex;
                        if (previousTabindex === '') {
                            focusable.removeAttribute('tabindex');
                        } else {
                            focusable.setAttribute('tabindex', previousTabindex);
                        }
                        delete focusable.dataset.v2MotionTabindex;
                    }
                });
            });
        };

        if (quietHero && persistentControls.length && 'IntersectionObserver' in window) {
            const heroRect = quietHero.getBoundingClientRect();
            const initialVisibleHeight = Math.min(heroRect.bottom, window.innerHeight) - Math.max(heroRect.top, 0);
            setPersistentQuiet(initialVisibleHeight / heroRect.height >= 0.35);

            const persistentObserver = new IntersectionObserver(entries => {
                entries.forEach(entry => setPersistentQuiet(entry.intersectionRatio >= 0.35));
            }, { threshold: 0.35 });

            persistentObserver.observe(quietHero);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMotion);
    } else {
        initMotion();
    }
})();
