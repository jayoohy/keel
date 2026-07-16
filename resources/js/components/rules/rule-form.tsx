import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export interface RuleFormData {
    name: string;
    goal_id: string;
    trigger_type: 'transaction_type' | 'category' | 'merchant';
    trigger_value: string;
    action_basis: 'percentage' | 'fixed';
    action_value: string;
    is_active: boolean;
}

interface GoalOption {
    id: number;
    name: string;
}

interface RuleFormProps {
    data: RuleFormData;
    setData: <K extends keyof RuleFormData>(key: K, value: RuleFormData[K]) => void;
    errors: Partial<Record<keyof RuleFormData, string>>;
    processing: boolean;
    onSubmit: FormEventHandler;
    submitLabel: string;
    goals: GoalOption[];
}

const TRIGGER_VALUE_PLACEHOLDER: Record<RuleFormData['trigger_type'], string> = {
    transaction_type: 'e.g. salary',
    category: 'e.g. Food',
    merchant: 'e.g. uber',
};

export function RuleForm({ data, setData, errors, processing, onSubmit, submitLabel, goals }: RuleFormProps) {
    return (
        <form onSubmit={onSubmit} className="max-w-xl space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Save from salary" />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="goal_id">Goal</Label>
                <Select value={data.goal_id} onValueChange={(value) => setData('goal_id', value)}>
                    <SelectTrigger id="goal_id">
                        <SelectValue placeholder="Choose a goal" />
                    </SelectTrigger>
                    <SelectContent>
                        {goals.map((goal) => (
                            <SelectItem key={goal.id} value={goal.id.toString()}>
                                {goal.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.goal_id} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="trigger_type">When</Label>
                <Select
                    value={data.trigger_type}
                    onValueChange={(value) => setData('trigger_type', value as RuleFormData['trigger_type'])}
                >
                    <SelectTrigger id="trigger_type">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="transaction_type">Transaction type is</SelectItem>
                        <SelectItem value="category">Category is</SelectItem>
                        <SelectItem value="merchant">Description contains</SelectItem>
                    </SelectContent>
                </Select>
                <InputError message={errors.trigger_type} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="trigger_value">Match value</Label>
                <Input
                    id="trigger_value"
                    value={data.trigger_value}
                    onChange={(e) => setData('trigger_value', e.target.value)}
                    placeholder={TRIGGER_VALUE_PLACEHOLDER[data.trigger_type]}
                />
                <InputError message={errors.trigger_value} />
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="action_basis">Allocate</Label>
                    <Select
                        value={data.action_basis}
                        onValueChange={(value) => setData('action_basis', value as RuleFormData['action_basis'])}
                    >
                        <SelectTrigger id="action_basis">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="percentage">Percentage of transaction</SelectItem>
                            <SelectItem value="fixed">Fixed amount</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.action_basis} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="action_value">{data.action_basis === 'percentage' ? 'Percent' : 'Amount'}</Label>
                    <Input
                        id="action_value"
                        type="number"
                        min="0.01"
                        value={data.action_value}
                        onChange={(e) => setData('action_value', e.target.value)}
                        placeholder={data.action_basis === 'percentage' ? '20' : '5000'}
                    />
                    <InputError message={errors.action_value} />
                </div>
            </div>

            <div className="flex items-center gap-3">
                <Checkbox id="is_active" checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                <Label htmlFor="is_active">Active</Label>
            </div>

            <Button type="submit" disabled={processing}>
                {submitLabel}
            </Button>
        </form>
    );
}
