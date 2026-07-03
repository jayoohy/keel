import { formatCurrencyWhole } from '@/lib/format-currency';

interface SpendingByCategoryChartProps {
    data: { category: string; total: number }[];
}

export function SpendingByCategoryChart({ data }: SpendingByCategoryChartProps) {
    const max = Math.max(...data.map((d) => d.total), 1);

    return (
        <div>
            <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Spending by category</h2>

            {data.length === 0 ? (
                <p className="text-muted-foreground mt-3 text-sm">No spending recorded this month yet.</p>
            ) : (
                <ul className="mt-3 space-y-2.5">
                    {data.map((d) => (
                        <li key={d.category} className="flex items-center gap-3">
                            <span className="w-28 shrink-0 truncate text-sm">{d.category}</span>
                            <div className="bg-secondary relative h-4 flex-1 overflow-hidden rounded-xs">
                                <div className="bg-primary h-full" style={{ width: `${(d.total / max) * 100}%` }} />
                            </div>
                            <span className="font-tabular w-24 shrink-0 text-right text-sm">{formatCurrencyWhole(d.total)}</span>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
