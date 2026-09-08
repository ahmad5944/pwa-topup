const storageKey = 'topup_pwa_recently_viewed_products';
const maxItems = 6;

function normalizeProduct(product) {
    if (!product?.id) {
        return null;
    }

    return {
        id: product.id,
        name: product.name ?? '',
        brand: product.brand ?? '',
        display_price: product.display_price ?? product.price_jual ?? 0,
        category_name: product.category?.name ?? '',
    };
}

export function getRecentlyViewedProducts() {
    if (typeof window === 'undefined') {
        return [];
    }

    try {
        const stored = JSON.parse(window.localStorage.getItem(storageKey) ?? '[]');

        return Array.isArray(stored) ? stored.filter((product) => Boolean(product?.id)) : [];
    } catch {
        return [];
    }
}

export function recordRecentlyViewedProduct(product) {
    if (typeof window === 'undefined') {
        return;
    }

    const normalizedProduct = normalizeProduct(product);

    if (!normalizedProduct) {
        return;
    }

    const currentItems = getRecentlyViewedProducts().filter((item) => item.id !== normalizedProduct.id);
    currentItems.unshift(normalizedProduct);

    window.localStorage.setItem(storageKey, JSON.stringify(currentItems.slice(0, maxItems)));
}
