import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import Heading from '@/components/heading';
import { RuleForm, type RuleFormData } from '@/components/rules/rule-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Rules', href: '/rules' },
    { title: 'New rule', href: '/rules/create' },
];

export default function RulesCreate({ goals }: { goals: { id: number; name: string }[] }) {
    const { data, setData, post, processing, errors } = useForm<RuleFormData>({
        name: '',
        goal_id: '',
        trigger_type: 'transaction_type',
        trigger_value: '',
        action_basis: 'percentage',
        action_value: '',
        is_active: true,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('rules.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New rule" />

            <div className="space-y-6 p-4">
                <Heading title="New rule" description="Allocate automatically whenever a matching transaction comes in" />
                <RuleForm data={data} setData={setData} errors={errors} processing={processing} onSubmit={submit} submitLabel="Create rule" goals={goals} />
            </div>
        </AppLayout>
    );
}
