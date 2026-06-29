import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import Heading from '@/components/heading';
import { RuleForm, type RuleFormData } from '@/components/rules/rule-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Rules', href: '/rules' },
    { title: 'Edit rule', href: '#' },
];

interface RuleEditProps {
    rule: {
        id: number;
        name: string;
        goal_id: number;
        trigger_type: RuleFormData['trigger_type'];
        trigger_value: string;
        action_basis: RuleFormData['action_basis'];
        action_value: string;
        is_active: boolean;
    };
    goals: { id: number; name: string }[];
}

export default function RulesEdit({ rule, goals }: RuleEditProps) {
    const { data, setData, put, processing, errors } = useForm<RuleFormData>({
        name: rule.name,
        goal_id: rule.goal_id.toString(),
        trigger_type: rule.trigger_type,
        trigger_value: rule.trigger_value,
        action_basis: rule.action_basis,
        action_value: rule.action_value,
        is_active: rule.is_active,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('rules.update', rule.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit rule" />

            <div className="space-y-6 p-4">
                <Heading title="Edit rule" />
                <RuleForm data={data} setData={setData} errors={errors} processing={processing} onSubmit={submit} submitLabel="Save changes" goals={goals} />
            </div>
        </AppLayout>
    );
}
