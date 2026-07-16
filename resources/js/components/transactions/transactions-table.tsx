import { router } from '@inertiajs/react';
import { type ColumnDef, flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';

import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { formatCurrency } from '@/lib/format-currency';

export interface TransactionRow {
    id: number;
    description: string | null;
    narration: string | null;
    type: string;
    amount: string;
    currency: string;
    transacted_at: string;
    account: { name: string } | null;
    category: { id: number; name: string } | null;
}

interface CategoryOption {
    id: number;
    name: string;
}

interface TransactionsTableProps {
    transactions: TransactionRow[];
    categories: CategoryOption[];
}

export function TransactionsTable({ transactions, categories }: TransactionsTableProps) {
    const recategorize = (transactionId: number, categoryId: string) => {
        router.patch(route('transactions.update', transactionId), { category_id: categoryId });
    };

    const columns: ColumnDef<TransactionRow>[] = [
        {
            header: 'Date',
            accessorKey: 'transacted_at',
            cell: ({ row }) => new Date(row.original.transacted_at).toLocaleDateString(),
        },
        {
            header: 'Description',
            accessorKey: 'description',
            cell: ({ row }) => row.original.description ?? row.original.narration ?? '—',
        },
        {
            header: 'Account',
            accessorKey: 'account.name',
            cell: ({ row }) => row.original.account?.name ?? '—',
        },
        {
            header: 'Category',
            accessorKey: 'category',
            cell: ({ row }) => (
                <Select value={row.original.category?.id.toString() ?? ''} onValueChange={(value) => recategorize(row.original.id, value)}>
                    <SelectTrigger className="h-8 w-40">
                        <SelectValue placeholder="Uncategorized" />
                    </SelectTrigger>
                    <SelectContent>
                        {categories.map((category) => (
                            <SelectItem key={category.id} value={category.id.toString()}>
                                {category.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            ),
        },
        {
            header: 'Type',
            accessorKey: 'type',
            cell: ({ row }) => <Badge variant="outline">{row.original.type}</Badge>,
        },
        {
            header: 'Amount',
            accessorKey: 'amount',
            cell: ({ row }) => (
                <span className={amountClass(row.original.type)}>
                    {formatCurrency(row.original.amount, row.original.currency)}
                </span>
            ),
        },
    ];

    const table = useReactTable({
        data: transactions,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    const amountClass = (type: string) => (type === 'credit' || type === 'salary' ? 'text-green-600' : 'text-foreground');

    return (
        <>
            {/* Mobile: card list */}
            <div className="space-y-2 md:hidden">
                {transactions.map((transaction) => (
                    <div key={transaction.id} className="space-y-2 rounded-lg border p-3">
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <p className="truncate text-sm font-medium">{transaction.description ?? transaction.narration ?? '—'}</p>
                                <p className="text-muted-foreground text-xs">
                                    {new Date(transaction.transacted_at).toLocaleDateString()}
                                    {transaction.account?.name ? ` · ${transaction.account.name}` : ''}
                                </p>
                            </div>
                            <div className="shrink-0 text-right">
                                <p className={`text-sm font-semibold ${amountClass(transaction.type)}`}>
                                    {formatCurrency(transaction.amount, transaction.currency)}
                                </p>
                                <Badge variant="outline" className="mt-1">
                                    {transaction.type}
                                </Badge>
                            </div>
                        </div>
                        <Select
                            value={transaction.category?.id.toString() ?? ''}
                            onValueChange={(value) => recategorize(transaction.id, value)}
                        >
                            <SelectTrigger className="h-8 w-full">
                                <SelectValue placeholder="Uncategorized" />
                            </SelectTrigger>
                            <SelectContent>
                                {categories.map((category) => (
                                    <SelectItem key={category.id} value={category.id.toString()}>
                                        {category.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                ))}

                {transactions.length === 0 && <p className="text-muted-foreground py-6 text-center text-sm">No transactions found.</p>}
            </div>

            {/* Desktop: table */}
            <div className="hidden overflow-x-auto rounded-lg border md:block">
            <table className="w-full text-sm">
                <thead className="bg-muted/50">
                    {table.getHeaderGroups().map((headerGroup) => (
                        <tr key={headerGroup.id}>
                            {headerGroup.headers.map((header) => (
                                <th key={header.id} className="px-4 py-2 text-left font-medium">
                                    {flexRender(header.column.columnDef.header, header.getContext())}
                                </th>
                            ))}
                        </tr>
                    ))}
                </thead>
                <tbody>
                    {table.getRowModel().rows.map((row) => (
                        <tr key={row.id} className="border-t">
                            {row.getVisibleCells().map((cell) => (
                                <td key={cell.id} className="px-4 py-2">
                                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                </td>
                            ))}
                        </tr>
                    ))}

                    {transactions.length === 0 && (
                        <tr>
                            <td colSpan={columns.length} className="text-muted-foreground px-4 py-6 text-center">
                                No transactions found.
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
            </div>
        </>
    );
}
