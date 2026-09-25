(function () {
    const search = document.getElementById('market-search');
    const filters = Array.from(document.querySelectorAll('[data-market-filter]'));
    const groups = Array.from(document.querySelectorAll('[data-market-group]'));
    const noResults = document.getElementById('market-no-results');

    if (!groups.length) return;

    let activeFilter = 'all';

    function normalize(value) {
        return (value || '')
            .toLocaleLowerCase('pt-BR')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function update() {
        const term = normalize(search ? search.value : '');
        let visibleTotal = 0;

        groups.forEach((group) => {
            const groupKey = group.dataset.marketGroup;
            const groupAllowed = activeFilter === 'all' || activeFilter === groupKey;
            let groupVisible = 0;

            group.querySelectorAll('[data-market-item]').forEach((item) => {
                const haystack = normalize(item.dataset.search);
                const matches = groupAllowed && (!term || haystack.includes(term));
                item.hidden = !matches;
                if (matches) groupVisible += 1;
            });

            const hasRows = group.querySelector('[data-market-item]');
            group.hidden = !groupAllowed || (hasRows && groupVisible === 0);
            visibleTotal += groupVisible;
        });

        if (noResults) noResults.hidden = visibleTotal !== 0 || !document.querySelector('[data-market-item]');
    }

    filters.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.marketFilter || 'all';
            filters.forEach((filter) => {
                const active = filter === button;
                filter.classList.toggle('active', active);
                filter.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            update();
        });
    });

    if (search) {
        search.addEventListener('input', update);
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                search.value = '';
                search.blur();
                update();
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && search) {
            event.preventDefault();
            search.focus();
            search.select();
        }
    });
})();
