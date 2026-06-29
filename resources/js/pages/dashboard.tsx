import { Head, Link } from '@inertiajs/react';

import { GoalProgressCard, type GoalSummary } from '@/components/goals/goal-progress-card';
import { HealthScoreCard } from '@/components/dashboard/health-score-card';
import { IncomeExpenseTrendChart } from '@/components/dashboard/income-expense-trend-chart';
import { SpendingByCategoryChart } from '@/components/dashboard/spending-by-category-chart';
import { InsightCard, type InsightItem } from '@/components/insights/insight-card';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePolling } from '@/hooks/use-polling';
import { formatCurrency } from '@/lib/format-currency';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface DashboardSummary {
    totalBalance: number;
    monthlyIncome: number;
    monthlyExpenses: number;
    monthlySavings: number;
    savingsRate: number;
    netCashFlow: number;
}

interface HealthScore {
    score: number;
    savings_rate: string;
    emergency_fund_coverage: string;
    spending_stability: string;
    income_consistency: string;
}

interface DashboardProps {
    summary: DashboardSummary;
    spendingByCategory: { category: string; total: number }[];
    trend: { month: string; income: number; expenses: number }[];
    goals: GoalSummary[];
    healthScore: HealthScore | null;
    insights: InsightItem[];
}

export default function Dashboard({ summary, spendingByCategory, trend, goals, healthScore, insights }: DashboardProps) {
    usePolling(['summary', 'spendingByCategory', 'trend', 'goals', 'healthScore', 'insights'], 30000);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-normal">Total balance</CardTitle>
                        </CardHeader>
                        <CardContent className="text-xl font-semibold">{formatCurrency(summary.totalBalance)}</CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-normal">Monthly income</CardTitle>
                        </CardHeader>
                        <CardContent className="text-xl font-semibold">{formatCurrency(summary.monthlyIncome)}</CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-normal">Monthly expenses</CardTitle>
                        </CardHeader>
                        <CardContent className="text-xl font-semibold">{formatCurrency(summary.monthlyExpenses)}</CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-normal">Savings rate</CardTitle>
                        </CardHeader>
                        <CardContent className="text-xl font-semibold">{summary.savingsRate}%</CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <div className="md:col-span-1">
                        <HealthScoreCard healthScore={healthScore} />
                    </div>
                    <div className="md:col-span-2">
                        <IncomeExpenseTrendChart data={trend} />
                    </div>
                </div>

                <SpendingByCategoryChart data={spendingByCategory} />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {goals.map((goal) => (
                        <GoalProgressCard key={goal.id} goal={goal} />
                    ))}
                </div>

                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-semibold">Insights</h2>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={route('insights.index')}>View all</Link>
                        </Button>
                    </div>
                    {insights.map((insight) => (
                        <InsightCard key={insight.id} insight={insight} />
                    ))}
                    {insights.length === 0 && <p className="text-muted-foreground text-sm">No insights yet.</p>}
                </div>
            </div>
        </AppLayout>
    );
}
