/**
 * Display helpers. Currency codes come from the API payload rather than being
 * hardcoded, so switching the configured pair changes the interface too.
 */

const LOCALE = 'fr-FR';

/**
 * @param {number} amount
 * @param {string} currency ISO 4217 code, e.g. "USD".
 * @param {number} [maximumFractionDigits]
 */
export function money(amount, currency, maximumFractionDigits = 2) {
    return new Intl.NumberFormat(LOCALE, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
        maximumFractionDigits,
    }).format(amount);
}

/**
 * A unit price below one unit of fiat needs more decimals to stay meaningful:
 * DOGE at $0.095 must not render as $0.10.
 *
 * @param {number} amount
 * @param {string} currency
 */
export function unitPrice(amount, currency) {
    return money(amount, currency, amount < 1 ? 6 : 2);
}

/**
 * Crypto quantities keep up to 8 decimals but drop trailing zeros.
 *
 * @param {number} quantity
 */
export function quantity(quantity) {
    return new Intl.NumberFormat(LOCALE, { maximumFractionDigits: 8 }).format(quantity);
}

/**
 * @param {string} iso8601
 */
export function dateTime(iso8601) {
    return new Intl.DateTimeFormat(LOCALE, {
        dateStyle: 'short',
        timeStyle: 'medium',
    }).format(new Date(iso8601));
}
