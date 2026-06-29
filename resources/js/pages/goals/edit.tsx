import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import Heading from '@/components/heading';
import { GoalForm, type GoalFormData } from '@/components/goals/goal-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Goals', href: '/goals' },
    { title: 'Edit goal', href: '#' },
];

interface GoalEditProps {
    goal: {
        id: number;
        name: string;
        description: string | null;
        target_amount: string;
        deadline: string | null;
        priority: GoalFormData['priority'];
        status: GoalFormData['status'];
    };
}

export default function GoalsEdit({ goal }: GoalEditProps) {
    const { data, setData, put, processing, errors } = useForm<GoalFormData>({
        name: goal.name,
        description: goal.description ?? '',
        target_amount: goal.target_amount,
        deadline: goal.deadline ?? '',
        priority: goal.priority,
        status: goal.status,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('goals.update', goal.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit goal" />

            <div className="space-y-6 p-4">
                <Heading title="Edit goal" />
                <GoalForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    onSubmit={submit}
                    submitLabel="Save changes"
                    showStatus
                />
            </div>
        </AppLayout>
    );
}
