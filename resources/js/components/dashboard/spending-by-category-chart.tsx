import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from 'recharts';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from '@/components/ui/chart';

interface SpendingByCategoryChartProps {
    data: { category: string; total: number }[];
}

const chartConfig = {
    total: {
        label: 'Spent',
        color: 'var(--chart-1)',
    },
} satisfies ChartConfig;

export function SpendingByCategoryChart({ data }: SpendingByCategoryChartProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Spending by category</CardTitle>
            </CardHeader>
            <CardContent>
                <ChartContainer config={chartConfig} className="max-h-64 w-full">
                    <BarChart accessibilityLayer data={data} layout="vertical" margin={{ left: 16 }}>
                        <CartesianGrid horizontal={false} />
                        <XAxis type="number" hide />
                        <YAxis dataKey="category" type="category" tickLine={false} axisLine={false} width={100} />
                        <ChartTooltip cursor={false} content={<ChartTooltipContent indicator="line" />} />
                        <Bar dataKey="total" fill="var(--color-total)" radius={4} />
                    </BarChart>
                </ChartContainer>

                {data.length === 0 && <p className="text-muted-foreground mt-2 text-sm">No spending recorded this month yet.</p>}
            </CardContent>
        </Card>
    );
}
