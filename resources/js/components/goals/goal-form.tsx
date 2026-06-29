import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

export interface GoalFormData {
    name: string;
    description: string;
    target_amount: string;
    deadline: string;
    priority: 'low' | 'medium' | 'high';
    status?: 'active' | 'completed' | 'paused' | 'cancelled';
}

interface GoalFormProps {
    data: GoalFormData;
    setData: <K extends keyof GoalFormData>(key: K, value: GoalFormData[K]) => void;
    errors: Partial<Record<keyof GoalFormData, string>>;
    processing: boolean;
    onSubmit: FormEventHandler;
    submitLabel: string;
    showStatus?: boolean;
}

export function GoalForm({ data, setData, errors, processing, onSubmit, submitLabel, showStatus = false }: GoalFormProps) {
    return (
        <form onSubmit={onSubmit} className="max-w-xl space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="House Fund" />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Description</Label>
                <textarea
                    id="description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    className={cn(
                        'flex min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                    )}
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="target_amount">Target amount</Label>
                <Input
                    id="target_amount"
                    type="number"
                    min="1"
                    value={data.target_amount}
                    onChange={(e) => setData('target_amount', e.target.value)}
                    placeholder="1000000"
                />
                <InputError message={errors.target_amount} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="deadline">Deadline</Label>
                <Input id="deadline" type="date" value={data.deadline} onChange={(e) => setData('deadline', e.target.value)} />
                <InputError message={errors.deadline} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="priority">Priority</Label>
                <Select value={data.priority} onValueChange={(value) => setData('priority', value as GoalFormData['priority'])}>
                    <SelectTrigger id="priority">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="low">Low</SelectItem>
                        <SelectItem value="medium">Medium</SelectItem>
                        <SelectItem value="high">High</SelectItem>
                    </SelectContent>
                </Select>
                <InputError message={errors.priority} />
            </div>

            {showStatus && data.status && (
                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <Select value={data.status} onValueChange={(value) => setData('status', value as GoalFormData['status'])}>
                        <SelectTrigger id="status">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="paused">Paused</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.status} />
                </div>
            )}

            <Button type="submit" disabled={processing}>
                {submitLabel}
            </Button>
        </form>
    );
}
