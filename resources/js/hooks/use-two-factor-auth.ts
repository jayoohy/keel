import { useCallback, useState } from 'react';

interface TwoFactorQrCode {
    svg: string;
    url: string;
}

/**
 * The QR code / secret key / recovery codes endpoints are plain JSON routes
 * registered by Laravel Fortify (not Inertia responses), so they're fetched
 * directly instead of through Inertia's router.
 */
export function useTwoFactorAuth() {
    const [qrCode, setQrCode] = useState<TwoFactorQrCode | null>(null);
    const [recoveryCodes, setRecoveryCodes] = useState<string[]>([]);

    const fetchQrCode = useCallback(async () => {
        const response = await fetch(route('two-factor.qr-code'));
        setQrCode(await response.json());
    }, []);

    const fetchRecoveryCodes = useCallback(async () => {
        const response = await fetch(route('two-factor.recovery-codes'));
        setRecoveryCodes(await response.json());
    }, []);

    const clearQrCode = useCallback(() => setQrCode(null), []);
    const clearRecoveryCodes = useCallback(() => setRecoveryCodes([]), []);

    return { qrCode, fetchQrCode, clearQrCode, recoveryCodes, fetchRecoveryCodes, clearRecoveryCodes };
}
