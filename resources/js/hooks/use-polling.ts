import { router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

/**
 * Refetches the given Inertia props on an interval. Used in place of
 * WebSockets/Reverb, which Hostinger shared hosting can't run (see PRD §7).
 */
export function usePolling(only: string[], intervalMs: number = 30000) {
    const onlyRef = useRef(only);
    onlyRef.current = only;

    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: onlyRef.current });
        }, intervalMs);

        return () => clearInterval(interval);
    }, [intervalMs]);
}
