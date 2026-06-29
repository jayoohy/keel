import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

export default function TwoFactorChallenge() {
    const [usingRecoveryCode, setUsingRecoveryCode] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        code: '',
        recovery_code: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('two-factor.login'));
    };

    const toggleRecoveryCode = () => {
        setUsingRecoveryCode((previous) => !previous);
        reset('code', 'recovery_code');
    };

    return (
        <AuthLayout
            title="Two-factor authentication"
            description={
                usingRecoveryCode
                    ? 'Confirm access to your account by entering one of your emergency recovery codes.'
                    : 'Confirm access to your account by entering the authentication code provided by your authenticator app.'
            }
        >
            <Head title="Two-factor authentication" />

            <form onSubmit={submit} className="flex flex-col gap-6">
                {usingRecoveryCode ? (
                    <div className="grid gap-2">
                        <Label htmlFor="recovery_code">Recovery code</Label>
                        <Input
                            id="recovery_code"
                            autoFocus
                            autoComplete="one-time-code"
                            value={data.recovery_code}
                            onChange={(e) => setData('recovery_code', e.target.value)}
                        />
                        <InputError message={errors.recovery_code} />
                    </div>
                ) : (
                    <div className="grid gap-2">
                        <Label htmlFor="code">Code</Label>
                        <Input
                            id="code"
                            autoFocus
                            inputMode="numeric"
                            autoComplete="one-time-code"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                            placeholder="123456"
                        />
                        <InputError message={errors.code} />
                    </div>
                )}

                <Button type="submit" className="w-full" disabled={processing}>
                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                    Log in
                </Button>

                <Button type="button" variant="link" onClick={toggleRecoveryCode} className="text-center text-sm">
                    {usingRecoveryCode ? 'Use an authentication code instead' : 'Use a recovery code instead'}
                </Button>
            </form>
        </AuthLayout>
    );
}
