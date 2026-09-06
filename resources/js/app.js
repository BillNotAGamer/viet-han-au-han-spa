import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import.meta.glob([
    '../images/**',
    '!../images/images resource/**',
], { eager: true });
