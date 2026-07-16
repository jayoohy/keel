import { Head, router, useForm } from '@inertiajs/react';
import { useCallback } from 'react';

import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useMonoConnect } from '@/hooks/use-mono-connect';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bank accounts',
        href: '/bank-connections',
    },
];

interface BankConnection {
    id: number;
    institution_name: string | null;
    status: 'active' | 'reauth_required' | 'disconnected';
    connected_at: string | null;
}

interface BankConnectionsProps {
    connections: BankConnection[];
    monoPublicKey: string | null;
}

const statusVariant: Record<BankConnection['status'], 'default' | 'destructive' | 'secondary'> = {
    active: 'default',
    reauth_required: 'destructive',
    disconnected: 'secondary',
};

export default function BankConnectionsIndex({ connections, monoPublicKey }: BankConnectionsProps) {
    const form = useForm({ code: '' });

    const handleSuccess = useCallback(
        (code: string) => {
            form.setData('code', code);
            form.post(route('bank-connections.store'));
        },
        [form],
    );

    const { ready, open } = useMonoConnect(monoPublicKey ?? '', handleSuccess);

    const disconnect = (id: number) => {
        router.delete(route('bank-connections.destroy', id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Bank accounts" />

            <div className="space-y-6 p-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Heading title="Bank accounts" description="Connect a bank account to start tracking transactions" />
                    <Button className="w-full sm:w-auto" onClick={open} disabled={!ready || form.processing}>
                        Connect a bank account
                    </Button>
                </div>

                <div className="grid gap-4">
                    {connections.map((connection) => (
                        <Card key={connection.id}>
                            <CardContent className="flex items-center justify-between p-4">
                                <div className="space-y-1">
                                    <p className="font-medium">{connection.institution_name ?? 'Bank account'}</p>
                                    <Badge variant={statusVariant[connection.status]}>{connection.status.replace('_', ' ')}</Badge>
                                </div>

                                {connection.status !== 'disconnected' && (
                                    <Button variant="outline" size="sm" onClick={() => disconnect(connection.id)}>
                                        Disconnect
                                    </Button>
                                )}
                            </CardContent>
                        </Card>
                    ))}

                    {connections.length === 0 && <p className="text-muted-foreground text-sm">No bank accounts connected yet.</p>}
                </div>
            </div>
        </AppLayout>
    );
}
