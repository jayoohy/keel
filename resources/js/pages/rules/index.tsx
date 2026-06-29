import { Head, Link, router } from '@inertiajs/react';

import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Rules', href: '/rules' }];

interface RuleItem {
    id: number;
    name: string;
    trigger_type: string;
    trigger_value: string;
    action_basis: string;
    action_value: string;
    is_active: boolean;
    goal: { name: string } | null;
    executions_count: number;
}

export default function RulesIndex({ rules }: { rules: RuleItem[] }) {
    const destroy = (id: number) => {
        router.delete(route('rules.destroy', id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Rules" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Automation rules" description="Automatically allocate money to goals as transactions come in" />
                    <Button asChild>
                        <Link href={route('rules.create')}>New rule</Link>
                    </Button>
                </div>

                <div className="grid gap-4">
                    {rules.map((rule) => (
                        <Card key={rule.id}>
                            <CardContent className="flex items-center justify-between p-4">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <p className="font-medium">{rule.name}</p>
                                        <Badge variant={rule.is_active ? 'default' : 'secondary'}>{rule.is_active ? 'active' : 'inactive'}</Badge>
                                    </div>
                                    <p className="text-muted-foreground text-sm">
                                        When {rule.trigger_type.replace('_', ' ')} is &ldquo;{rule.trigger_value}&rdquo;, allocate{' '}
                                        {rule.action_basis === 'percentage' ? `${rule.action_value}%` : `₦${rule.action_value}`} to {rule.goal?.name}{' '}
                                        · fired {rule.executions_count} time{rule.executions_count === 1 ? '' : 's'}
                                    </p>
                                </div>
                                <div className="flex gap-2">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={route('rules.show', rule.id)}>History</Link>
                                    </Button>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={route('rules.edit', rule.id)}>Edit</Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" onClick={() => destroy(rule.id)}>
                                        Delete
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}

                    {rules.length === 0 && <p className="text-muted-foreground text-sm">No rules yet — create your first automation.</p>}
                </div>
            </div>
        </AppLayout>
    );
}
