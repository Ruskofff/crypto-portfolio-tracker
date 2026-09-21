/**
 * Thin wrapper around the Laravel JSON API.
 *
 * Every screen in this app reads from these endpoints, so validation errors
 * (422) are turned into a typed error the components can render per field.
 */

const BASE_URL = '/api';

export class ValidationError extends Error {
    /**
     * @param {string} message
     * @param {Record<string, string[]>} errors
     */
    constructor(message, errors) {
        super(message);
        this.name = 'ValidationError';
        this.errors = errors;
    }
}

async function request(path, options = {}) {
    const response = await fetch(`${BASE_URL}${path}`, {
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        },
        ...options,
    });

    if (response.status === 204) {
        return null;
    }

    const payload = await response.json().catch(() => null);

    if (response.status === 422) {
        throw new ValidationError(
            payload?.message ?? 'The submitted data is invalid.',
            payload?.errors ?? {},
        );
    }

    if (!response.ok) {
        throw new Error(payload?.message ?? `Request failed with status ${response.status}.`);
    }

    return payload;
}

export const api = {
    portfolio: () => request('/portfolio'),
    cryptocurrencies: () => request('/cryptocurrencies'),
    platforms: () => request('/platforms'),

    createHolding: (holding) =>
        request('/holdings', { method: 'POST', body: JSON.stringify(holding) }),

    updateHolding: (id, holding) =>
        request(`/holdings/${id}`, { method: 'PUT', body: JSON.stringify(holding) }),

    deleteHolding: (id) => request(`/holdings/${id}`, { method: 'DELETE' }),
};
