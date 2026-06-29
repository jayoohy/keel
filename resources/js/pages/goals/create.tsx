import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import Heading from '@/components/heading';
import { GoalForm, type GoalFormData } from '@/components/goals/goal-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Goals', href: '/goals' },
    { title: 'New goal', href: '/goals/create' },
];

export default function GoalsCreate() {
    const { data, setData, post, processing, errors } = useForm<GoalFormData>({
        name: '',
        description: '',
        target_amount: '',
        deadline: '',
        priority: 'medium',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('goals.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New goal" />

            <div className="space-y-6 p-4">
                <Heading title="New goal" description="Set a target and start allocating toward it" />
                <GoalForm data={data} setData={setData} errors={errors} processing={processing} onSubmit={submit} submitLabel="Create goal" />
            </div>
        </AppLayout>
    );
}
