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

    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMotion);
    } else {
        initMotion();
    }
})();
