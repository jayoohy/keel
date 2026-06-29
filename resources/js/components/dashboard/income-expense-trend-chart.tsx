import { CartesianGrid, Line, LineChart, XAxis, YAxis } from 'recharts';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent, type ChartConfig } from '@/components/ui/chart';

interface IncomeExpenseTrendChartProps {
    data: { month: string; income: number; expenses: number }[];
}

const chartConfig = {
    income: {
        label: 'Income',
        color: 'var(--chart-2)',
    },
    expenses: {
        label: 'Expenses',
        color: 'var(--chart-4)',
    },
} satisfies ChartConfig;

export function IncomeExpenseTrendChart({ data }: IncomeExpenseTrendChartProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Income vs expenses</CardTitle>
            </CardHeader>
            <CardContent>
                <ChartContainer config={chartConfig} className="max-h-64 w-full">
                    <LineChart accessibilityLayer data={data} margin={{ left: 12, right: 12 }}>
                        <CartesianGrid vertical={false} />
                        <XAxis dataKey="month" tickLine={false} axisLine={false} />
                        <YAxis hide />
                        <ChartTooltip content={<ChartTooltipContent />} />
                        <ChartLegend content={<ChartLegendContent />} />
                        <Line dataKey="income" type="monotone" stroke="var(--color-income)" strokeWidth={2} dot={false} />
                        <Line dataKey="expenses" type="monotone" stroke="var(--color-expenses)" strokeWidth={2} dot={false} />
                    </LineChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
}
