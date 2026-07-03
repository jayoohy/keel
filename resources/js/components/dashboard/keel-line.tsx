import { Link } from '@inertiajs/react';

import { formatCurrencyWhole } from '@/lib/format-currency';
import { cn } from '@/lib/utils';

export interface KeelLineGoal {
    id: number;
    name: string;
    current_amount: string | number;
}

interface KeelLineProps {
    totalBalance: number;
    goals: KeelLineGoal[];
}

const SEGMENT_COLORS = ['var(--color-depth)', 'var(--color-brass)', 'var(--color-ballast)', 'var(--chart-4)', 'var(--chart-5)'];

export function KeelLine({ totalBalance, goals }: KeelLineProps) {
    const allocations = goals.map((goal, index) => ({
        id: goal.id,
        name: goal.name,
        amount: Number(goal.current_amount),
        color: SEGMENT_COLORS[index % SEGMENT_COLORS.length],
    }));

    const allocated = allocations.reduce((sum, a) => sum + a.amount, 0);
    const unallocated = Math.max(totalBalance - allocated, 0);
    const denominator = totalBalance > 0 ? totalBalance : allocated || 1;

    if (goals.length === 0) {
        return (
            <div className="space-y-3">
                <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Where it's going</h2>
                <div className="border-border bg-secondary/40 flex h-10 items-center rounded-sm border border-dashed px-3">
                    <p className="text-muted-foreground text-sm">
                        No goals yet —{' '}
                        <Link href={route('goals.create')} className="text-primary underline underline-offset-4">
                            add one
                        </Link>{' '}
                        to start steering this balance somewhere.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-3">
            <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Where it's going</h2>

            <div className="border-border flex h-10 w-full overflow-hidden rounded-sm border" role="img" aria-label="Balance allocated across goals">
                {allocations.map((a) => (
                    <div
                        key={a.id}
                        className="h-full first:border-l-0 not-first:border-l not-first:border-l-background/40"
                        style={{ width: `${(a.amount / denominator) * 100}%`, backgroundColor: a.color }}
                        title={`${a.name}: ${formatCurrencyWhole(a.amount)}`}
                    />
                ))}
                {unallocated > 0 && (
                    <div
                        className="bg-muted h-full"
                        style={{ width: `${(unallocated / denominator) * 100}%` }}
                        title={`Unallocated: ${formatCurrencyWhole(unallocated)}`}
                    />
                )}
            </div>

            <div className="flex flex-wrap gap-x-6 gap-y-2">
                {allocations.map((a) => (
                    <Link
                        key={a.id}
                        href={route('goals.show', a.id)}
                        className="group flex items-center gap-2 text-sm"
                    >
                        <span className="inline-block h-3 w-1.5 shrink-0" style={{ backgroundColor: a.color }} aria-hidden />
                        <span className="group-hover:underline">{a.name}</span>
                        <span className="font-tabular text-muted-foreground text-xs">{formatCurrencyWhole(a.amount)}</span>
                    </Link>
                ))}
                {unallocated > 0 && (
                    <div className="flex items-center gap-2 text-sm">
                        <span className={cn('bg-muted-foreground/40 inline-block h-3 w-1.5 shrink-0')} aria-hidden />
                        <span className="text-muted-foreground">Unallocated</span>
                        <span className="font-tabular text-muted-foreground text-xs">{formatCurrencyWhole(unallocated)}</span>
                    </div>
                )}
            </div>
        </div>
    );
}
