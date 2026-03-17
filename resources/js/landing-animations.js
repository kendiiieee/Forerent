import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const premiumEase = 'power4.out';
const heroEase = 'expo.out';

function getSharedScrollOptions() {
    return {
        once: true,
        invalidateOnRefresh: true,
        fastScrollEnd: true,
    };
}

function clearReducedMotionTargets(targets) {
    if (!targets.length) {
        return;
    }

    gsap.set(targets, { clearProps: 'all' });
}

function setInitialState(selector, vars) {
    const elements = gsap.utils.toArray(selector);
    if (!elements.length) {
        return;
    }

    gsap.set(elements, vars);
}

function primeDemoInitialStates() {
    const mobile = window.matchMedia('(max-width: 768px)').matches;
    const navOffset = mobile ? -15 : -20;
    const searchBarOffset = mobile ? 45 : 50;

    setInitialState('.hero-logo', { opacity: 0 });
    setInitialState('.hero-nav-link, .hero-login-btn', { opacity: 0, y: navOffset });
    setInitialState('.hero-prediction-card', { opacity: 0, y: 40 });
    setInitialState('.hero-search-bar', { opacity: 0, y: searchBarOffset });
    setInitialState('.search-field-item', { opacity: 0, y: 32 });
    setInitialState('.stat-item', { opacity: 0, y: 40 });
    setInitialState('.dark-text-left', { opacity: 0, x: -30 });
    setInitialState('.dark-text-right', { opacity: 0, x: 30 });
    setInitialState('.dark-feature-card', { opacity: 0, y: 40 });
    setInitialState('.ecosystem-header', { opacity: 0, y: 20 });
    setInitialState('.ecosystem-card', { opacity: 0, y: 30 });
    setInitialState('.ecosystem-checklist-item', { opacity: 0, y: 10 });
    setInitialState('.grid-header', { opacity: 0, y: 20 });
    setInitialState('.grid-card', { opacity: 0, y: 30 });
    setInitialState('.footer-section', { opacity: 0, y: 20 });
    setInitialState('.footer-column', { opacity: 0, y: 20 });
}

function primeLiveInitialStates(selectors) {
    const mobile = window.matchMedia('(max-width: 768px)').matches;
    const navOffset = mobile ? -15 : -20;

    setInitialState(selectors.logo, { opacity: 0, y: -8 });
    setInitialState([selectors.navLinks, selectors.loginButton], { opacity: 0, y: navOffset });
    setInitialState(selectors.heroCard, { opacity: 0, y: 40 });
    setInitialState(selectors.searchBar, { opacity: 0 });
    setInitialState([selectors.searchFields, selectors.searchButton], { opacity: 0, y: 32 });
    setInitialState(selectors.stats, { opacity: 0, y: 40 });
    setInitialState([selectors.neuralEyebrow, selectors.neuralTitle], { opacity: 0, x: -40 });
    setInitialState(selectors.neuralCopy, { opacity: 0, x: 40 });
    setInitialState(selectors.moduleCards, { opacity: 0, y: 50 });
    gsap.utils.toArray(selectors.moduleCards).forEach((card) => {
        const nestedItems = card.querySelectorAll('div.w-14, span.inline-block, h3, p, div.flex.justify-between.items-center');
        if (!nestedItems.length) {
            return;
        }

        gsap.set(nestedItems, { opacity: 0, y: 12 });
    });
    setInitialState(selectors.ecosystemHeader, { opacity: 0, y: 30 });
    setInitialState(selectors.ecosystemCards, { opacity: 0, y: 30 });
    setInitialState('#ecosystemCards .eco-card.active .eco-card-features li', { opacity: 0, y: 15 });
    setInitialState(selectors.featureHeader, { opacity: 0, y: 34 });
    setInitialState(selectors.featureCards, { opacity: 0, y: 40 });
    setInitialState(selectors.footerBrand, { opacity: 0, y: 24 });
    setInitialState(selectors.footerColumns, { opacity: 0, y: 24 });
    setInitialState(selectors.footerBottom, { opacity: 0, y: 24 });
}

function addHoverLift(selector) {
    if (!window.matchMedia('(hover: hover)').matches) {
        return;
    }

    document.querySelectorAll(selector).forEach((card) => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, { y: -8, duration: 0.25, ease: premiumEase, overwrite: 'auto' });
        });

        card.addEventListener('mouseleave', () => {
            gsap.to(card, { y: 0, duration: 0.25, ease: premiumEase, overwrite: 'auto' });
        });
    });
}

function animateBatch(selector, options = {}) {
    const elements = gsap.utils.toArray(selector);
    if (!elements.length) {
        return;
    }

    const {
        trigger = elements[0],
        start = 'top 85%',
        duration = 0.7,
        stagger = 0.12,
    } = options;

    ScrollTrigger.batch(elements, {
        ...getSharedScrollOptions(),
        trigger,
        start,
        onEnter: (items) => {
            gsap.to(items, {
                opacity: 1,
                y: 0,
                duration,
                ease: premiumEase,
                stagger,
                overwrite: 'auto',
            });
        },
    });
}

function animateSingle(selector, options = {}) {
    const elements = gsap.utils.toArray(selector);
    if (!elements.length) {
        return;
    }

    const {
        trigger = elements[0],
        start = 'top 80%',
        duration = 0.8,
        stagger = 0.12,
        from = { opacity: 0, y: 30 },
    } = options;

    gsap.fromTo(
        elements,
        from,
        {
            opacity: 1,
            y: 0,
            duration,
            ease: premiumEase,
            stagger,
            scrollTrigger: {
                ...getSharedScrollOptions(),
                trigger,
                start,
            },
        }
    );
}

function parseStatValue(text) {
    const match = text.trim().match(/^(\d+(?:\.\d+)?)(.*)$/);
    if (!match) {
        return null;
    }

    return {
        value: Number.parseFloat(match[1]),
        suffix: match[2] || '',
        decimals: match[1].includes('.') ? match[1].split('.')[1].length : 0,
    };
}

function updateStatValue(element, value, suffix, decimals) {
    const formattedValue = decimals > 0 ? value.toFixed(decimals) : Math.round(value).toString();
    const suffixNode = element.querySelector('span');

    if (suffixNode && element.firstChild?.nodeType === Node.TEXT_NODE) {
        element.firstChild.textContent = formattedValue;
        return;
    }

    element.textContent = `${formattedValue}${suffix}`;
}

function animateStats(selector, options = {}) {
    const cards = gsap.utils.toArray(selector);
    if (!cards.length) {
        return;
    }

    const {
        trigger = cards[0].closest('.stats-section') || cards[0].parentElement,
        start = 'top 80%',
        duration = 1,
        stagger = 0.15,
        countDuration = 1.8,
    } = options;

    const timeline = gsap.timeline({
        scrollTrigger: {
            ...getSharedScrollOptions(),
            trigger,
            start,
        },
    });

    timeline.to(cards, {
        opacity: 1,
        y: 0,
        duration,
        ease: premiumEase,
        stagger,
        overwrite: 'auto',
    });

    cards.forEach((card, index) => {
        const valueElement = card.firstElementChild;
        if (!valueElement) {
            return;
        }

        const stat = parseStatValue(valueElement.textContent);
        if (!stat) {
            return;
        }

        const counter = { value: 0 };
        updateStatValue(valueElement, 0, stat.suffix, stat.decimals);

        timeline.to(
            counter,
            {
                value: stat.value,
                duration: countDuration,
                ease: heroEase,
                snap: { value: stat.decimals > 0 ? 0.1 : 1 },
                onUpdate: () => {
                    updateStatValue(valueElement, counter.value, stat.suffix, stat.decimals);
                },
            },
            index * stagger + 0.08
        );
    });
}

function buildDemoHeroTimeline() {
    const heroTimeline = gsap.timeline({ defaults: { ease: heroEase } });

    heroTimeline
        .fromTo(
            '.hero-bg-image',
            { scale: 1.05, opacity: 1 },
            { scale: 1, opacity: 1, duration: 2, ease: 'power2.out' }
        )
        .to('.hero-logo', { opacity: 1, duration: 0.6, ease: premiumEase }, 0.2)
        .to(
            ['.hero-nav-link', '.hero-login-btn'],
            {
                opacity: 1,
                y: 0,
                duration: 0.9,
                ease: heroEase,
                stagger: 0.1,
            },
            0.3
        )
        .fromTo(
            '.hero-prediction-card',
            { opacity: 0, y: 40 },
            { opacity: 1, y: 0, duration: 1.2, ease: heroEase },
            '>-0.05'
        )
        .fromTo(
            '.hero-search-bar',
            { opacity: 0, y: 50 },
            { opacity: 1, y: 0, duration: 1.1, ease: heroEase },
            '<0.18'
        )
        .fromTo(
            '.search-field-item',
            { opacity: 0, y: 32 },
            {
                opacity: 1,
                y: 0,
                duration: 0.85,
                ease: premiumEase,
                stagger: 0.12,
            },
            '<0.16'
        );
}

function initDemoAnimations() {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const reduceMotionTargets = [
        '.hero-logo',
        '.hero-nav-link',
        '.hero-login-btn',
        '.hero-prediction-card',
        '.hero-search-bar',
        '.search-field-item',
        '.stat-item',
        '.dark-text-left',
        '.dark-text-right',
        '.dark-feature-card',
        '.ecosystem-header',
        '.ecosystem-card',
        '.ecosystem-checklist-item',
        '.grid-header',
        '.grid-card',
        '.footer-section',
        '.footer-column',
    ];

    if (reduceMotion) {
        clearReducedMotionTargets(reduceMotionTargets);
        return;
    }

    primeDemoInitialStates();

    const ctx = gsap.context(() => {
        ScrollTrigger.config({ ignoreMobileResize: true, autoRefreshEvents: 'visibilitychange,DOMContentLoaded,load' });

        buildDemoHeroTimeline();

        animateStats('.stat-item', { start: 'top 80%', duration: 1, stagger: 0.15, countDuration: 1.8 });

        gsap.fromTo(
            '.dark-text-left',
            { opacity: 0, x: -30 },
            {
                opacity: 1,
                x: 0,
                duration: 0.7,
                ease: premiumEase,
                scrollTrigger: {
                    ...getSharedScrollOptions(),
                    trigger: '.dark-section',
                    start: 'top 70%',
                },
            }
        );

        gsap.fromTo(
            '.dark-text-right',
            { opacity: 0, x: 30 },
            {
                opacity: 1,
                x: 0,
                duration: 0.7,
                ease: premiumEase,
                scrollTrigger: {
                    ...getSharedScrollOptions(),
                    trigger: '.dark-section',
                    start: 'top 70%',
                },
            }
        );

        animateBatch('.dark-feature-card', { trigger: '.dark-section', start: 'top 72%', duration: 0.6, stagger: 0.14 });

        animateSingle('.ecosystem-header', { trigger: '.light-section', start: 'top 80%', duration: 0.65, from: { opacity: 0, y: 20 } });

        ScrollTrigger.batch(gsap.utils.toArray('.ecosystem-card'), {
            ...getSharedScrollOptions(),
            trigger: '.light-section',
            start: 'top 82%',
            onEnter: (cards) => {
                gsap.to(cards, { opacity: 1, y: 0, duration: 0.65, ease: premiumEase, stagger: 0.14, overwrite: 'auto' });

                cards.forEach((card) => {
                    const checklistItems = card.querySelectorAll('.ecosystem-checklist-item');
                    if (!checklistItems.length) {
                        return;
                    }

                    gsap.to(checklistItems, {
                        opacity: 1,
                        y: 0,
                        duration: 0.45,
                        ease: premiumEase,
                        stagger: 0.1,
                        delay: 0.25,
                        overwrite: 'auto',
                    });
                });
            },
        });

        animateSingle('.grid-header', { trigger: '.grid-section', start: 'top 80%', duration: 0.65, from: { opacity: 0, y: 20 } });
        animateBatch('.grid-card', { trigger: '.grid-section', start: 'top 88%', duration: 0.55, stagger: 0.1 });
        animateSingle('.footer-section', { trigger: '.footer-section', start: 'top 92%', duration: 0.7, from: { opacity: 0, y: 20 } });
        animateBatch('.footer-column', { trigger: '.footer-section', start: 'top 90%', duration: 0.5, stagger: 0.08 });

        addHoverLift('.dark-feature-card, .grid-card, .ecosystem-card');
        ScrollTrigger.refresh();
    }, document.body);

    window.addEventListener('pagehide', () => ctx.revert(), { once: true });
}

function initLiveLandingAnimations() {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const selectors = {
        background: '#hero-section > img.absolute',
        logo: '#mainNav > a img[alt="ForeRent Logo"]',
        navLinks: '#mainNav .nav-link',
        loginButton: '#mainNav .login-btn',
        heroCard: '#hero-section .hero-card-glass',
        searchBar: '#hero-section > div.absolute.bottom-0.z-20',
        searchFields: '#hero-section .search-field',
        searchButton: '#hero-section > div.absolute.bottom-0.z-20 button',
        stats: '#hero-section + div > div:first-child > div',
        neuralEyebrow: '#about > div > div:first-child > div:first-child > div',
        neuralTitle: '#about > div > div:first-child > div:first-child > h2',
        neuralCopy: '#about > div > div:first-child > div:last-child',
        moduleCards: '#about .module-card',
        ecosystemHeader: '#about + section > div > div:first-child',
        ecosystemCards: '#ecosystemCards .eco-card',
        featureHeader: '#features > div > div:first-child',
        featureCards: '#features > div > div.grid > div',
        footerBrand: 'footer > div:first-child > div > div:first-child',
        footerColumns: 'footer > div:first-child > div > div:last-child > div',
        footerBottom: 'footer > div:last-child',
    };

    const reduceMotionTargets = Object.values(selectors);

    if (reduceMotion) {
        clearReducedMotionTargets(reduceMotionTargets);
        return;
    }

    primeLiveInitialStates(selectors);

    const ctx = gsap.context(() => {
        ScrollTrigger.config({ ignoreMobileResize: true, autoRefreshEvents: 'visibilitychange,DOMContentLoaded,load' });

        const heroTimeline = gsap.timeline({ defaults: { ease: heroEase } });
        const searchRevealItems = [
            ...gsap.utils.toArray(selectors.searchFields),
            ...gsap.utils.toArray(selectors.searchButton),
        ];

        heroTimeline
            .fromTo(
                selectors.background,
                { scale: 1.05, opacity: 1 },
                { scale: 1, opacity: 1, duration: 2, ease: 'power2.out' }
            )
            .fromTo(
                selectors.logo,
                { opacity: 0, y: -8 },
                { opacity: 1, y: 0, duration: 0.7, ease: premiumEase },
                0.18
            )
            .to(
                [...gsap.utils.toArray(selectors.navLinks), ...gsap.utils.toArray(selectors.loginButton)],
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.9,
                    ease: heroEase,
                    stagger: 0.1,
                },
                0.28
            )
            .fromTo(
                selectors.heroCard,
                { opacity: 0, y: 40 },
                { opacity: 1, y: 0, duration: 1.2, ease: heroEase },
                '>-0.05'
            )
            .fromTo(
                selectors.searchBar,
                { opacity: 0, xPercent: -50, yPercent: 50, y: 50 },
                { opacity: 1, xPercent: -50, yPercent: 50, y: 0, duration: 1.1, ease: heroEase },
                '<0.18'
            )
            .fromTo(
                searchRevealItems,
                { opacity: 0, y: 32 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.85,
                    ease: premiumEase,
                    stagger: 0.12,
                },
                '<0.16'
            );

        animateStats(selectors.stats, { trigger: '#hero-section + div', start: 'top 80%', duration: 1, stagger: 0.15, countDuration: 1.8 });

        gsap.to([selectors.neuralEyebrow, selectors.neuralTitle], {
            opacity: 1,
            x: 0,
            duration: 0.9,
            ease: premiumEase,
            stagger: 0.12,
            scrollTrigger: {
                ...getSharedScrollOptions(),
                trigger: '#about',
                start: 'top 80%',
            },
        });

        gsap.to(selectors.neuralCopy, {
            opacity: 1,
            x: 0,
            duration: 0.9,
            ease: premiumEase,
            scrollTrigger: {
                ...getSharedScrollOptions(),
                trigger: '#about',
                start: 'top 80%',
            },
        });

        ScrollTrigger.batch(gsap.utils.toArray(selectors.moduleCards), {
            ...getSharedScrollOptions(),
            trigger: '#about',
            start: 'top 80%',
            onEnter: (cards) => {
                gsap.to(cards, {
                    opacity: 1,
                    y: 0,
                    duration: 0.95,
                    ease: premiumEase,
                    stagger: 0.2,
                    overwrite: 'auto',
                });

                cards.forEach((card, index) => {
                    const nestedItems = card.querySelectorAll('div.w-14, span.inline-block, h3, p, div.flex.justify-between.items-center');
                    if (!nestedItems.length) {
                        return;
                    }

                    gsap.to(nestedItems, {
                        opacity: 1,
                        y: 0,
                        duration: 0.55,
                        ease: premiumEase,
                        stagger: 0.06,
                        delay: 0.22 + index * 0.08,
                        overwrite: 'auto',
                    });
                });
            },
        });

        animateSingle(selectors.ecosystemHeader, { trigger: '#about + section', start: 'top 80%', duration: 0.8, from: { opacity: 0, y: 30 } });

        ScrollTrigger.batch(gsap.utils.toArray(selectors.ecosystemCards), {
            ...getSharedScrollOptions(),
            trigger: '#ecosystemCards',
            start: 'top 80%',
            onEnter: (cards) => {
                gsap.to(cards, {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: premiumEase,
                    stagger: 0.16,
                    overwrite: 'auto',
                });

                const activeCard = document.querySelector('#ecosystemCards .eco-card.active');
                if (!activeCard) {
                    return;
                }

                const activeItems = activeCard.querySelectorAll('.eco-card-features li');
                if (!activeItems.length) {
                    return;
                }

                gsap.to(activeItems, {
                    opacity: 1,
                    y: 0,
                    duration: 0.5,
                    ease: premiumEase,
                    stagger: 0.1,
                    delay: 0.35,
                    overwrite: 'auto',
                });
            },
        });

        animateSingle(selectors.featureHeader, { trigger: '#features', start: 'top 80%', duration: 0.8, from: { opacity: 0, y: 30 } });
        gsap.to(selectors.featureCards, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: premiumEase,
            stagger: {
                each: 0.1,
                grid: [2, 3],
                from: 'start',
            },
            scrollTrigger: {
                ...getSharedScrollOptions(),
                trigger: '#features > div > div.grid',
                start: 'top 80%',
            },
        });
        animateSingle(selectors.footerBrand, { trigger: 'footer', start: 'top 88%', duration: 0.75, from: { opacity: 0, y: 30 } });
        animateBatch(selectors.footerColumns, { trigger: 'footer', start: 'top 90%', duration: 0.68, stagger: 0.1 });
        animateSingle(selectors.footerBottom, { trigger: 'footer', start: 'top 95%', duration: 0.65, from: { opacity: 0, y: 20 } });

        addHoverLift('#about .module-card, #features > div > div.grid > div');
        ScrollTrigger.refresh();
    }, document.body);

    window.addEventListener('pagehide', () => ctx.revert(), { once: true });
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.hero-section')) {
        initDemoAnimations();
        return;
    }

    if (document.getElementById('hero-section') && document.getElementById('mainNav')) {
        initLiveLandingAnimations();
    }
});
