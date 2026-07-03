import { Line, LineChart, XAxis, YAxis } from 'recharts';

import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from '@/components/ui/chart';

interface IncomeExpenseTrendChartProps {
    data: { month: string; income: number; expenses: number }[];
}

const chartConfig = {
    income: {
        label: 'Income',
        color: 'var(--color-depth)',
    },
    expenses: {
        label: 'Expenses',
        color: 'var(--color-brass)',
    },
} satisfies ChartConfig;

export function IncomeExpenseTrendChart({ data }: IncomeExpenseTrendChartProps) {
    return (
        <div>
            <div className="mb-3 flex items-center justify-between">
                <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Income vs expenses</h2>
                <div className="text-muted-foreground flex gap-4 text-xs">
                    <span className="flex items-center gap-1.5">
                        <span className="inline-block h-1.5 w-1.5 rounded-full" style={{ backgroundColor: 'var(--color-depth)' }} />
                        Income
                    </span>
                    <span className="flex items-center gap-1.5">
                        <span className="inline-block h-1.5 w-1.5 rounded-full" style={{ backgroundColor: 'var(--color-brass)' }} />
                        Expenses
                    </span>
                </div>
            </div>
            <ChartContainer config={chartConfig} className="h-48 w-full">
                <LineChart accessibilityLayer data={data} margin={{ left: 0, right: 12, top: 4 }}>
                    <XAxis dataKey="month" tickLine={false} axisLine={false} tickMargin={8} className="font-tabular text-xs" />
                    <YAxis hide />
                    <ChartTooltip content={<ChartTooltipContent />} />
                    <Line dataKey="income" type="monotone" stroke="var(--color-income)" strokeWidth={2} dot={false} />
                    <Line dataKey="expenses" type="monotone" stroke="var(--color-expenses)" strokeWidth={2} dot={false} />
                </LineChart>
            </ChartContainer>
        </div>
    );
}
