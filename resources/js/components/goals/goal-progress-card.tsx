import { Link } from '@inertiajs/react';

import { Badge } from '@/components/ui/badge';
import { formatCurrencyWhole } from '@/lib/format-currency';

export interface GoalSummary {
    id: number;
    name: string;
    target_amount: string;
    current_amount: string;
    deadline: string | null;
    priority: 'low' | 'medium' | 'high';
    status: 'active' | 'completed' | 'paused' | 'cancelled';
}

const statusVariant: Record<GoalSummary['status'], 'default' | 'secondary' | 'destructive'> = {
    active: 'default',
    completed: 'secondary',
    paused: 'secondary',
    cancelled: 'destructive',
};

export function GoalProgressCard({ goal }: { goal: GoalSummary }) {
    const target = Number(goal.target_amount);
    const current = Number(goal.current_amount);
    const percentage = target > 0 ? Math.min(Math.round((current / target) * 100), 100) : 0;

    return (
        <Link
            href={route('goals.show', goal.id)}
            className="group border-border hover:border-accent block rounded-sm border p-4 transition-colors"
        >
            <div className="mb-3 flex items-center justify-between">
                <h3 className="font-display text-base font-medium">{goal.name}</h3>
                <Badge variant={statusVariant[goal.status]}>{goal.status}</Badge>
            </div>

            <div className="bg-secondary relative h-2.5 w-full overflow-hidden rounded-xs">
                <div className="bg-primary h-full transition-[width]" style={{ width: `${percentage}%` }} />
                <div className="bg-foreground absolute top-1/2 right-0 h-3.5 w-0.5 -translate-y-1/2" aria-hidden />
            </div>

            <div className="mt-2 flex items-center justify-between text-sm">
                <span className="font-tabular text-muted-foreground">
                    {formatCurrencyWhole(current)} <span className="text-muted-foreground/60">of</span> {formatCurrencyWhole(target)}
                </span>
                <span className="font-tabular font-medium">{percentage}%</span>
            </div>

            {goal.deadline && <p className="text-muted-foreground mt-1 text-xs">Due {new Date(goal.deadline).toLocaleDateString('en-NG')}</p>}
        </Link>
    );
}
