import { Link } from '@inertiajs/react';

import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCurrency } from '@/lib/format-currency';

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
        <Link href={route('goals.show', goal.id)}>
            <Card className="transition-colors hover:bg-accent/50">
                <CardHeader className="flex flex-row items-center justify-between">
                    <CardTitle className="text-lg">{goal.name}</CardTitle>
                    <Badge variant={statusVariant[goal.status]}>{goal.status}</Badge>
                </CardHeader>
                <CardContent className="space-y-2">
                    <div className="bg-muted h-2 w-full overflow-hidden rounded-full">
                        <div className="bg-primary h-full" style={{ width: `${percentage}%` }} />
                    </div>
                    <div className="text-muted-foreground flex justify-between text-sm">
                        <span>
                            {formatCurrency(current)} of {formatCurrency(target)}
                        </span>
                        <span>{percentage}%</span>
                    </div>
                    {goal.deadline && <p className="text-muted-foreground text-xs">Due {new Date(goal.deadline).toLocaleDateString()}</p>}
                </CardContent>
            </Card>
        </Link>
    );
}
