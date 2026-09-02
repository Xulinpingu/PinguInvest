const menuAnimation = lottie.loadAnimation({
    container: document.getElementById("menu-icon"),
    renderer: "svg",
    loop: false,
    autoplay: false,
    path: "../assets/images/icons/menu.json"
});

menuAnimation.setSpeed(1.8);

let aberto = false;

const menuElement = document.getElementById("menu");
const hamburgerButton = document.querySelector(".hamburger");

function toggleMenu() {
    menuElement.classList.toggle("active");

    if (!aberto) {
        menuAnimation.playSegments([0, 91], true);
    } else {
        menuAnimation.playSegments([91, 0], true);
    }

    aberto = !aberto;
}

document.addEventListener("click", function(event) {
    if (!aberto) {
        return;
    }

    const clickedInsideMenu = menuElement.contains(event.target);
    const clickedHamburger = hamburgerButton.contains(event.target);

    if (!clickedInsideMenu && !clickedHamburger) {
        toggleMenu();
    }
});