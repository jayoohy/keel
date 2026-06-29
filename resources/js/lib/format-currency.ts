export function formatCurrency(amount: number | string, currency: string = 'NGN'): string {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
    }).format(Number(amount));
}
