// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

import collapse from '@alpinejs/collapse';

document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);
});

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
