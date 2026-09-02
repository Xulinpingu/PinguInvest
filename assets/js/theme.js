/**
 * Controle de tema (claro/escuro) do PinguInvest.
 * - Padrão: escuro.
 * - Preferência salva no localStorage e aplicada em todas as páginas.
 * - Este script deve ser carregado SEM "defer" e o quanto antes no <head>,
 *   para aplicar o tema salvo antes da primeira renderização (evita flash).
 */
(function () {
    var THEME_KEY = "pingu-theme";
    var VALID_THEMES = ["dark", "light"];

    function getStoredTheme() {
        try {
            var stored = localStorage.getItem(THEME_KEY);
            return VALID_THEMES.indexOf(stored) !== -1 ? stored : null;
        } catch (e) {
            return null;
        }
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute("data-theme", theme);
    }

    // Aplica imediatamente o tema salvo (ou escuro, que é o padrão do site)
    var initialTheme = getStoredTheme() || "dark";
    applyTheme(initialTheme);

    window.PinguTheme = {
        get: function () {
            return document.documentElement.getAttribute("data-theme") || "dark";
        },
        set: function (theme) {
            if (VALID_THEMES.indexOf(theme) === -1) return;
            applyTheme(theme);
            try {
                localStorage.setItem(THEME_KEY, theme);
            } catch (e) {
                /* localStorage indisponível (modo privado, etc.) — segue só na sessão atual */
            }
            document.dispatchEvent(
                new CustomEvent("pingu-theme-change", { detail: { theme: theme } })
            );
        },
        toggle: function () {
            var next = this.get() === "dark" ? "light" : "dark";
            this.set(next);
            return next;
        }
    };

    // Mantém outras abas/páginas abertas em sincronia quando o tema muda
    window.addEventListener("storage", function (event) {
        if (event.key === THEME_KEY && VALID_THEMES.indexOf(event.newValue) !== -1) {
            applyTheme(event.newValue);
            document.dispatchEvent(
                new CustomEvent("pingu-theme-change", { detail: { theme: event.newValue } })
            );
        }
    });
})();
