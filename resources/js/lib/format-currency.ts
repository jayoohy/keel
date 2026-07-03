export function formatCurrency(amount: number | string, currency: string = 'NGN'): string {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
    }).format(Number(amount));
}

/** Whole-naira formatting for large, glanceable figures (hero balances, chart labels). */
export function formatCurrencyWhole(amount: number | string, currency: string = 'NGN'): string {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(amount));
}
