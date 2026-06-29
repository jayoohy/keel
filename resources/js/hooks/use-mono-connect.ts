import { useEffect, useRef, useState } from 'react';

interface MonoConnectConfig {
    key: string;
    onSuccess: (response: { code: string }) => void;
    onClose?: () => void;
}

interface MonoConnectInstance {
    setup: () => void;
    open: () => void;
}

declare global {
    interface Window {
        Connect?: new (config: MonoConnectConfig) => MonoConnectInstance;
    }
}

const SCRIPT_SRC = 'https://connect.mono.co/connect.js';

/**
 * Loads Mono's Connect widget script once and wraps it in a small imperative
 * API. Kept out of the page component per the project's code-separation rule.
 */
export function useMonoConnect(publicKey: string, onSuccess: (code: string) => void) {
    const instanceRef = useRef<MonoConnectInstance | null>(null);
    const [scriptLoaded, setScriptLoaded] = useState(false);

    useEffect(() => {
        if (document.querySelector(`script[src="${SCRIPT_SRC}"]`)) {
            setScriptLoaded(true);
            return;
        }

        const script = document.createElement('script');
        script.src = SCRIPT_SRC;
        script.async = true;
        script.onload = () => setScriptLoaded(true);
        document.body.appendChild(script);
    }, []);

    useEffect(() => {
        if (!scriptLoaded || !window.Connect || !publicKey) {
            return;
        }

        instanceRef.current = new window.Connect({
            key: publicKey,
            onSuccess: ({ code }) => onSuccess(code),
        });
        instanceRef.current.setup();
    }, [scriptLoaded, publicKey, onSuccess]);

    return {
        ready: scriptLoaded && Boolean(publicKey),
        open: () => instanceRef.current?.open(),
    };
}
