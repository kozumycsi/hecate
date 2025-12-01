(() => {
    const config = window.HecateFavoritesConfig || {};

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    function updateBadge(count) {
        const badge = document.querySelector('[data-favorites-badge]');
        if (!badge) return;
        badge.textContent = count;
        if (count > 0) {
            badge.removeAttribute('hidden');
        } else {
            badge.setAttribute('hidden', 'hidden');
        }
    }

    function applyState(button, isFavorite) {
        button.dataset.isFavorite = isFavorite ? '1' : '0';
        button.setAttribute('aria-pressed', isFavorite ? 'true' : 'false');
        button.classList.toggle('is-favorite', isFavorite);
        const icon = button.querySelector('.fa-heart');
        if (icon) {
            icon.classList.remove('fas', 'far');
            icon.classList.add(isFavorite ? 'fas' : 'far');
        }
    }

    async function toggleFavorite(button) {
        const productId = parseInt(
            button.getAttribute('data-product-id') ||
            (button.closest('[data-product-id]')?.getAttribute('data-product-id')) ||
            '0',
            10
        );

        if (!productId) {
            console.warn('Produto inválido para favoritos.');
            return;
        }

        if (!config.isAuthenticated) {
            window.location.href = config.loginUrl || '#';
            return;
        }

        if (!config.ajaxUrl) {
            console.warn('Endpoint de favoritos não configurado.');
            return;
        }

        if (button.dataset.loading === '1') {
            return;
        }

        button.dataset.loading = '1';
        const formData = new URLSearchParams();
        formData.append('product_id', String(productId));
        formData.append('action', 'toggle');

        try {
            const response = await fetch(config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData.toString()
            });

            if (!response.ok) {
                throw new Error('Falha ao atualizar favoritos');
            }

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Erro ao atualizar favoritos');
            }

            const isFavorite = !!data.isFavorite;
            applyState(button, isFavorite);

            if (!isFavorite) {
                const removeSelector = button.getAttribute('data-remove-target');
                if (removeSelector) {
                    const target = button.closest(removeSelector);
                    if (target) {
                        target.remove();
                    }
                }
            }

            if (typeof data.favoritesCount === 'number') {
                updateBadge(data.favoritesCount);
            }
        } catch (err) {
            console.error(err);
            alert('Não foi possível atualizar seus favoritos. Tente novamente.');
        } finally {
            delete button.dataset.loading;
        }
    }

    ready(() => {
        document.querySelectorAll('[data-favorite-button]').forEach(button => {
            const initial = button.getAttribute('data-is-favorite') === '1';
            applyState(button, initial);
        });
    });

    document.addEventListener('click', event => {
        const button = event.target.closest('[data-favorite-button]');
        if (!button) return;
        event.preventDefault();
        toggleFavorite(button);
    });
})();

