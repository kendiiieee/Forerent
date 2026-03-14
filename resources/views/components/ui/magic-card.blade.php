@props([])

<article
    onmousemove="
        const rect = this.getBoundingClientRect();
        const x = ((event.clientX - (rect.left + rect.width / 2)) / (rect.width / 2)).toFixed(3);
        const y = ((event.clientY - (rect.top + rect.height / 2)) / (rect.height / 2)).toFixed(3);
        this.style.setProperty('--pointer-x', x);
        this.style.setProperty('--pointer-y', y);
    "
    onmouseleave="
        this.style.setProperty('--pointer-x', '-10');
        this.style.setProperty('--pointer-y', '-10');
    "
    {{ $attributes->merge([
        'class' => 'group relative cursor-pointer rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden transition-all duration-300 active:scale-[0.99] active:translate-y-px'
    ]) }}
>
    <div class="pointer-events-none absolute inset-0 rounded-2xl overflow-hidden">
        <div class="absolute inset-0 bg-white transition-colors duration-300 group-hover:bg-blue-50"></div>

        <div
            class="absolute -inset-10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            style="
                transform: translate(
                    calc(var(--pointer-x, -10) * 18%),
                    calc(var(--pointer-y, -10) * 18%)
                ) scale(1.25);
                background: radial-gradient(circle at center, rgba(37, 99, 235, 0.48) 0%, rgba(59, 130, 246, 0.18) 38%, rgba(29, 78, 216, 0) 70%);
                filter: blur(24px) saturate(1.35) brightness(1.12) contrast(1.12);
                will-change: transform, filter;
            "
        ></div>
    </div>

    <div
        class="pointer-events-none absolute inset-0 rounded-2xl border-2 border-transparent backdrop-blur-[1px] backdrop-saturate-[1.25] backdrop-brightness-[1.04]"
        style="
            mask: linear-gradient(#fff 0 100%) border-box, linear-gradient(#fff 0 100%) padding-box;
            mask-composite: exclude;
            -webkit-mask-composite: xor;
        "
    ></div>

    <div class="relative z-10 h-full w-full">
        {{ $slot }}
    </div>
</article>
