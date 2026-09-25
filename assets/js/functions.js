let menuAnimation = null;
let aberto = false;

const menuElement = document.getElementById('menu');
const hamburgerButton = document.querySelector('.hamburger');

if (window.lottie && document.getElementById('menu-icon')) {
    try {
        menuAnimation = lottie.loadAnimation({
            container: document.getElementById('menu-icon'),
            renderer: 'svg',
            loop: false,
            autoplay: false,
            path: '../assets/images/icons/menu.json'
        });
        menuAnimation.setSpeed(1.8);
        document.documentElement.classList.add('lottie-menu-ready');
    } catch (error) {
        menuAnimation = null;
    }
}

function setMenuState(nextState) {
    if (!menuElement || !hamburgerButton) return;

    aberto = nextState;
    menuElement.classList.toggle('active', aberto);
    hamburgerButton.setAttribute('aria-expanded', aberto ? 'true' : 'false');
    hamburgerButton.setAttribute('aria-label', aberto ? 'Fechar menu' : 'Abrir menu');

    if (menuAnimation) {
        menuAnimation.playSegments(aberto ? [0, 91] : [91, 0], true);
    }
}

function toggleMenu() {
    setMenuState(!aberto);
}

window.toggleMenu = toggleMenu;

document.addEventListener('click', function (event) {
    if (!aberto || !menuElement || !hamburgerButton) return;

    const clickedInsideMenu = menuElement.contains(event.target);
    const clickedHamburger = hamburgerButton.contains(event.target);

    if (!clickedInsideMenu && !clickedHamburger) {
        setMenuState(false);
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && aberto) {
        setMenuState(false);
        hamburgerButton?.focus();
    }
});

window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 900px)').matches && aberto) {
        setMenuState(false);
    }
});
