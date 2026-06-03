const menuAnimation = lottie.loadAnimation({
    container: document.getElementById("menu-icon"),
    renderer: "svg",
    loop: false,
    autoplay: false,
    path: "../assets/images/icons/menu.json"
});

menuAnimation.setSpeed(1.8);

let aberto = false;

function toggleMenu() {
    document.getElementById("menu").classList.toggle("active");

    if (!aberto) {
        menuAnimation.playSegments([0, 91], true);
    } else {
        menuAnimation.playSegments([91, 0], true);
    }

    aberto = !aberto;
}
