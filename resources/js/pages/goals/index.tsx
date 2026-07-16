import { Head, Link } from '@inertiajs/react';

import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { GoalProgressCard, type GoalSummary } from '@/components/goals/goal-progress-card';
import { formatCurrency } from '@/lib/format-currency';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Goals',
        href: '/goals',
    },
];

interface GoalsIndexProps {
    goals: GoalSummary[];
    unallocatedBalance: number;
}

export default function GoalsIndex({ goals, unallocatedBalance }: GoalsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Goals" />

            <div className="space-y-6 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Heading title="Savings goals" description={`${formatCurrency(unallocatedBalance)} unallocated`} />
                    <Button asChild>
                        <Link href={route('goals.create')}>New goal</Link>
                    </Button>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {goals.map((goal) => (
                        <GoalProgressCard key={goal.id} goal={goal} />
                    ))}

                    {goals.length === 0 && (
                        <p className="text-muted-foreground text-sm">No goals yet — add one to start steering your balance toward something real.</p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
