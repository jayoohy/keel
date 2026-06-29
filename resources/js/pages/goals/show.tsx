import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatCurrency } from '@/lib/format-currency';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface Allocation {
    id: number;
    amount: string;
    type: string;
    source: string;
    created_at: string;
}

interface GoalShowProps {
    goal: {
        id: number;
        name: string;
        description: string | null;
        target_amount: string;
        current_amount: string;
        deadline: string | null;
        status: string;
        allocations: Allocation[];
        forecast: { projected_completion_date: string | null; average_monthly_saving: string } | null;
    };
    unallocatedBalance: number;
}

export default function GoalsShow({ goal, unallocatedBalance }: GoalShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Goals', href: '/goals' },
        { title: goal.name, href: `/goals/${goal.id}` },
    ];

    const { data, setData, post, processing, errors, reset } = useForm({ amount: '' });

    const allocate: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('allocations.store', goal.id), { onSuccess: () => reset() });
    };

    const removeAllocation = (id: number) => {
        router.delete(route('allocations.destroy', id));
    };

    const percentage = Number(goal.target_amount) > 0 ? Math.min(Math.round((Number(goal.current_amount) / Number(goal.target_amount)) * 100), 100) : 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={goal.name} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading title={goal.name} description={goal.description ?? undefined} />
                    <Button variant="outline" asChild>
                        <Link href={route('goals.edit', goal.id)}>Edit</Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="space-y-2 p-4">
                        <div className="bg-muted h-2 w-full overflow-hidden rounded-full">
                            <div className="bg-primary h-full" style={{ width: `${percentage}%` }} />
                        </div>
                        <div className="text-muted-foreground flex justify-between text-sm">
                            <span>
                                {formatCurrency(goal.current_amount)} of {formatCurrency(goal.target_amount)}
                            </span>
                            <span>{percentage}%</span>
                        </div>
                    </CardContent>
                </Card>

                {goal.forecast && (
                    <p className="text-muted-foreground text-sm">
                        {goal.forecast.projected_completion_date
                            ? `At ${formatCurrency(goal.forecast.average_monthly_saving)}/month, you're projected to reach this goal by ${new Date(
                                  goal.forecast.projected_completion_date,
                              ).toLocaleDateString()}.`
                            : "At your current savings rate, this goal's completion date can't be projected yet."}
                    </p>
                )}

                <form onSubmit={allocate} className="flex max-w-sm items-end gap-2">
                    <div className="grid flex-1 gap-2">
                        <Label htmlFor="amount">Allocate amount ({formatCurrency(unallocatedBalance)} unallocated)</Label>
                        <Input id="amount" type="number" min="0.01" value={data.amount} onChange={(e) => setData('amount', e.target.value)} />
                        <InputError message={errors.amount} />
                    </div>
                    <Button type="submit" disabled={processing}>
                        Allocate
                    </Button>
                </form>

                <div className="space-y-2">
                    <Heading title="Allocation history" />
                    {goal.allocations.map((allocation) => (
                        <Card key={allocation.id}>
                            <CardContent className="flex items-center justify-between p-4">
                                <div>
                                    <p className="font-medium">{formatCurrency(allocation.amount)}</p>
                                    <p className="text-muted-foreground text-sm">
                                        {allocation.type} · {allocation.source} · {new Date(allocation.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                                <Button variant="ghost" size="sm" onClick={() => removeAllocation(allocation.id)}>
                                    Remove
                                </Button>
                            </CardContent>
                        </Card>
                    ))}

                    {goal.allocations.length === 0 && <p className="text-muted-foreground text-sm">No allocations yet.</p>}
                </div>
            </div>
        </AppLayout>
    );
}
