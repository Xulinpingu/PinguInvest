(function () {
    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    root.classList.add('enhanced-ui');

    // Loader: só cobre o período real de carregamento; não adiciona atraso artificial.
    const loader = document.getElementById('page-loader');
    const hideLoader = () => {
        if (!loader) return;
        loader.classList.add('is-hidden');
        window.setTimeout(() => loader.remove(), reducedMotion ? 0 : 450);
    };

    if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', hideLoader, { once: true });
        // Fallback para recursos externos lentos: libera a interface mesmo assim.
        window.setTimeout(hideLoader, 1400);
    }

    if (reducedMotion) {
        document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-visible'));
        return;
    }

    // Entrance reveal — aplica também a componentes existentes do projeto.
    const revealCandidates = document.querySelectorAll([
        '[data-reveal]',
        '.feature-card',
        '.dashboard-card',
        '.perfil-card',
        '.aula',
        '.team-card'
    ].join(','));

    revealCandidates.forEach((el, index) => {
        el.classList.add('reveal-item');
        el.style.setProperty('--reveal-delay', `${Math.min(index % 6, 5) * 55}ms`);
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -28px' });

        revealCandidates.forEach((el) => observer.observe(el));
    } else {
        revealCandidates.forEach((el) => el.classList.add('is-visible'));
    }

    // Tilt 3D leve: somente em dispositivos com mouse/trackpad e só nos itens opt-in.
    if (finePointer) {
        document.querySelectorAll('[data-tilt]').forEach((card) => {
            const strength = Number(card.dataset.tiltStrength || 3);

            card.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width;
                const y = (event.clientY - rect.top) / rect.height;
                const rotateY = (x - 0.5) * strength * 2;
                const rotateX = (0.5 - y) * strength * 2;

                card.style.setProperty('--tilt-x', `${rotateX.toFixed(2)}deg`);
                card.style.setProperty('--tilt-y', `${rotateY.toFixed(2)}deg`);
                card.style.setProperty('--pointer-x', `${(x * 100).toFixed(1)}%`);
                card.style.setProperty('--pointer-y', `${(y * 100).toFixed(1)}%`);
            });

            card.addEventListener('pointerleave', () => {
                card.style.setProperty('--tilt-x', '0deg');
                card.style.setProperty('--tilt-y', '0deg');
                card.style.setProperty('--pointer-x', '50%');
                card.style.setProperty('--pointer-y', '50%');
            });
        });
    }

    // Parallax extremamente sutil no hero da home, sem mexer no conteúdo interativo.
    const homeHero = document.querySelector('.home-hero');
    if (homeHero) {
        let ticking = false;
        const updateParallax = () => {
            const rect = homeHero.getBoundingClientRect();
            const viewport = window.innerHeight || 1;
            const progress = Math.max(-1, Math.min(1, (viewport / 2 - rect.top) / viewport));
            homeHero.style.setProperty('--parallax-y', `${(progress * 18).toFixed(1)}px`);
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(updateParallax);
        }, { passive: true });
        updateParallax();
    }
})();
