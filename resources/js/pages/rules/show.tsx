import { Head } from '@inertiajs/react';

import Heading from '@/components/heading';
import { Card, CardContent } from '@/components/ui/card';
import { formatCurrency } from '@/lib/format-currency';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface Execution {
    id: number;
    executed_at: string;
    transaction: { description: string | null; amount: string } | null;
    allocation: { amount: string } | null;
}

interface RuleShowProps {
    rule: {
        id: number;
        name: string;
        goal: { name: string } | null;
    };
    executions: Execution[];
}

export default function RulesShow({ rule, executions }: RuleShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Rules', href: '/rules' },
        { title: rule.name, href: `/rules/${rule.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={rule.name} />

            <div className="space-y-6 p-4">
                <Heading title={rule.name} description={`Execution history · allocates to ${rule.goal?.name}`} />

                <div className="space-y-2">
                    {executions.map((execution) => (
                        <Card key={execution.id}>
                            <CardContent className="flex items-center justify-between p-4">
                                <div>
                                    <p className="font-medium">{execution.transaction?.description ?? 'Transaction'}</p>
                                    <p className="text-muted-foreground text-sm">{new Date(execution.executed_at).toLocaleString()}</p>
                                </div>
                                <p className="font-medium text-green-600">
                                    +{formatCurrency(execution.allocation?.amount ?? 0)}
                                </p>
                            </CardContent>
                        </Card>
                    ))}

                    {executions.length === 0 && <p className="text-muted-foreground text-sm">This rule hasn't fired yet.</p>}
                </div>
            </div>
        </AppLayout>
    );
}
