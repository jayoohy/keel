import { Head } from '@inertiajs/react';

import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCurrency } from '@/lib/format-currency';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },
];

interface AccountItem {
    id: number;
    name: string;
    account_number: string | null;
    account_type: string | null;
    currency: string;
    balance: string;
    bank_connection: { institution_name: string | null } | null;
}

interface AccountsProps {
    accounts: AccountItem[];
    totalBalance: number;
    accountCount: number;
}

export default function AccountsIndex({ accounts, totalBalance, accountCount }: AccountsProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Accounts" />

            <div className="space-y-6 p-4">
                <Heading title="Accounts" description="All balances across your connected bank accounts" />

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Total balance</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold">{formatCurrency(totalBalance)}</CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Connected accounts</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold">{accountCount}</CardContent>
                    </Card>
                </div>

                <div className="grid gap-4">
                    {accounts.map((account) => (
                        <Card key={account.id}>
                            <CardContent className="flex items-center justify-between gap-3 p-4">
                                <div className="min-w-0">
                                    <p className="font-medium">{account.name}</p>
                                    <p className="text-muted-foreground text-sm">
                                        {account.bank_connection?.institution_name} {account.account_number && `• ${account.account_number}`}
                                    </p>
                                </div>
                                <p className="text-lg font-semibold">{formatCurrency(account.balance, account.currency)}</p>
                            </CardContent>
                        </Card>
                    ))}

                    {accounts.length === 0 && <p className="text-muted-foreground text-sm">No accounts yet — connect a bank account first.</p>}
                </div>
            </div>
        </AppLayout>
    );
}
