import { Head, Link } from '@inertiajs/react';

import Heading from '@/components/heading';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TransactionsTable, type TransactionRow } from '@/components/transactions/transactions-table';
import { useTransactionFilters } from '@/hooks/use-transaction-filters';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: '/transactions',
    },
];

interface PaginatedTransactions {
    data: TransactionRow[];
    links: { url: string | null; label: string; active: boolean }[];
}

interface TransactionsProps {
    transactions: PaginatedTransactions;
    accounts: { id: number; name: string }[];
    categories: { id: number; name: string }[];
    filters: Record<string, string | undefined>;
}

const TRANSACTION_TYPES = ['debit', 'credit', 'transfer', 'fee', 'salary', 'refund'];

export default function TransactionsIndex({ transactions, accounts, categories, filters: initialFilters }: TransactionsProps) {
    const { filters, updateFilter } = useTransactionFilters(initialFilters);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Transactions" />

            <div className="space-y-6 p-4">
                <Heading title="Transactions" description="Search and filter every transaction across your connected accounts" />

                <div className="grid grid-cols-2 gap-3 lg:grid-cols-6">
                    <Select value={filters.account_id ?? 'all'} onValueChange={(value) => updateFilter('account_id', value === 'all' ? undefined : value)}>
                        <SelectTrigger>
                            <SelectValue placeholder="All accounts" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All accounts</SelectItem>
                            {accounts.map((account) => (
                                <SelectItem key={account.id} value={account.id.toString()}>
                                    {account.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={filters.category_id ?? 'all'}
                        onValueChange={(value) => updateFilter('category_id', value === 'all' ? undefined : value)}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="All categories" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All categories</SelectItem>
                            {categories.map((category) => (
                                <SelectItem key={category.id} value={category.id.toString()}>
                                    {category.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select value={filters.type ?? 'all'} onValueChange={(value) => updateFilter('type', value === 'all' ? undefined : value)}>
                        <SelectTrigger>
                            <SelectValue placeholder="All types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All types</SelectItem>
                            {TRANSACTION_TYPES.map((type) => (
                                <SelectItem key={type} value={type}>
                                    {type}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Input
                        type="date"
                        value={filters.date_from ?? ''}
                        onChange={(e) => updateFilter('date_from', e.target.value)}
                        placeholder="From"
                    />
                    <Input type="date" value={filters.date_to ?? ''} onChange={(e) => updateFilter('date_to', e.target.value)} placeholder="To" />

                    <div className="col-span-2 flex gap-2 lg:col-span-1">
                        <Input
                            type="number"
                            value={filters.amount_min ?? ''}
                            onChange={(e) => updateFilter('amount_min', e.target.value)}
                            placeholder="Min ₦"
                        />
                        <Input
                            type="number"
                            value={filters.amount_max ?? ''}
                            onChange={(e) => updateFilter('amount_max', e.target.value)}
                            placeholder="Max ₦"
                        />
                    </div>
                </div>

                <TransactionsTable transactions={transactions.data} categories={categories} />

                <div className="flex flex-wrap gap-2">
                    {transactions.links.map((link, index) =>
                        link.url ? (
                            <Link
                                key={index}
                                href={link.url}
                                className={`rounded px-3 py-1 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'bg-muted'}`}
                            >
                                {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                            </Link>
                        ) : null,
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
