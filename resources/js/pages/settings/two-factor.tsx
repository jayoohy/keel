import { Head, router, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useEffect } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-factor authentication',
        href: '/settings/two-factor',
    },
];

interface TwoFactorProps {
    twoFactorEnabled: boolean;
    twoFactorConfirming: boolean;
    status?: string;
}

export default function TwoFactor({ twoFactorEnabled, twoFactorConfirming, status }: TwoFactorProps) {
    const { qrCode, fetchQrCode, clearQrCode, recoveryCodes, fetchRecoveryCodes, clearRecoveryCodes } = useTwoFactorAuth();

    const confirmForm = useForm({ code: '' });

    useEffect(() => {
        if (twoFactorConfirming) {
            fetchQrCode();
        }
    }, [twoFactorConfirming, fetchQrCode]);

    useEffect(() => {
        if (twoFactorEnabled) {
            fetchRecoveryCodes();
        }
    }, [twoFactorEnabled, fetchRecoveryCodes]);

    const enable = () => {
        router.post(route('two-factor.enable'));
    };

    const confirm: FormEventHandler = (e) => {
        e.preventDefault();
        confirmForm.post(route('two-factor.confirm'), {
            errorBag: 'confirmTwoFactorAuthentication',
            onSuccess: () => confirmForm.reset(),
        });
    };

    const disable = () => {
        clearQrCode();
        clearRecoveryCodes();
        router.delete(route('two-factor.disable'));
    };

    const regenerateRecoveryCodes = () => {
        router.post(route('two-factor.regenerate-recovery-codes'), {}, { onSuccess: fetchRecoveryCodes });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Two-factor authentication" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Two-factor authentication"
                        description="Secure your account with an authenticator app, in addition to your password"
                    />

                    {status && <div className="text-sm font-medium text-green-600">{status}</div>}

                    {!twoFactorEnabled && !twoFactorConfirming && (
                        <Button onClick={enable}>Enable two-factor authentication</Button>
                    )}

                    {twoFactorConfirming && (
                        <div className="space-y-4">
                            <p className="text-muted-foreground text-sm">
                                Scan this QR code with your authenticator app, then enter the 6-digit code it shows to finish enabling
                                two-factor authentication.
                            </p>

                            {qrCode && <div className="w-fit" dangerouslySetInnerHTML={{ __html: qrCode.svg }} />}

                            <form onSubmit={confirm} className="flex items-end gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="code">Code</Label>
                                    <Input
                                        id="code"
                                        autoFocus
                                        value={confirmForm.data.code}
                                        onChange={(e) => confirmForm.setData('code', e.target.value)}
                                        placeholder="123456"
                                    />
                                    <InputError message={confirmForm.errors.code} />
                                </div>

                                <Button disabled={confirmForm.processing}>
                                    {confirmForm.processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    Confirm
                                </Button>
                            </form>
                        </div>
                    )}

                    {twoFactorEnabled && (
                        <div className="space-y-4">
                            <p className="text-sm font-medium text-green-600">Two-factor authentication is enabled.</p>

                            {recoveryCodes.length > 0 && (
                                <div className="space-y-2">
                                    <Label>Recovery codes</Label>
                                    <p className="text-muted-foreground text-sm">
                                        Store these somewhere safe. Each code can be used once if you lose access to your authenticator app.
                                    </p>
                                    <div className="bg-muted grid gap-1 rounded-lg p-4 font-mono text-sm">
                                        {recoveryCodes.map((code) => (
                                            <div key={code}>{code}</div>
                                        ))}
                                    </div>
                                    <Button variant="secondary" size="sm" onClick={regenerateRecoveryCodes}>
                                        Regenerate recovery codes
                                    </Button>
                                </div>
                            )}

                            <Button variant="destructive" onClick={disable}>
                                Disable two-factor authentication
                            </Button>
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
