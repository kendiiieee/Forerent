import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.plugin(collapse);
    Alpine.start();
}

import 'flowbite';
import './landing-animations';



// Initialize Flowbite dropdowns
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('[data-collapse-toggle]');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-collapse-toggle');
            const target = document.getElementById(targetId);
            if (target) {
                target.classList.toggle('hidden');
            }
        });
    });
});
