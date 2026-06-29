import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

export interface TransactionFilters {
    account_id?: string;
    category_id?: string;
    type?: string;
    date_from?: string;
    date_to?: string;
    amount_min?: string;
    amount_max?: string;
}

export function useTransactionFilters(initial: TransactionFilters) {
    const [filters, setFilters] = useState<TransactionFilters>(initial);

    const updateFilter = useCallback(
        (key: keyof TransactionFilters, value: string | undefined) => {
            const next = { ...filters, [key]: value || undefined };
            setFilters(next);
            router.get(route('transactions.index'), next, { preserveState: true, replace: true });
        },
        [filters],
    );

    return { filters, updateFilter };
}
