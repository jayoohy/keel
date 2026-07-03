import { Head, Link } from '@inertiajs/react';

import { KeelLine } from '@/components/dashboard/keel-line';
import { HealthScoreCard } from '@/components/dashboard/health-score-card';
import { IncomeExpenseTrendChart } from '@/components/dashboard/income-expense-trend-chart';
import { SpendingByCategoryChart } from '@/components/dashboard/spending-by-category-chart';
import { GoalProgressCard, type GoalSummary } from '@/components/goals/goal-progress-card';
import { InsightCard, type InsightItem } from '@/components/insights/insight-card';
import { Button } from '@/components/ui/button';
import { usePolling } from '@/hooks/use-polling';
import { formatCurrencyWhole } from '@/lib/format-currency';
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

    const activeGoals = goals.filter((goal) => goal.status === 'active');
    const cashFlowPositive = summary.netCashFlow >= 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-8 p-4 md:p-6">
                {/* Instrument cluster */}
                <div className="grid grid-cols-2 gap-6 md:grid-cols-3">
                    <div>
                        <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Total balance</p>
                        <p className="font-display mt-1 text-3xl font-medium md:text-4xl">{formatCurrencyWhole(summary.totalBalance)}</p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Net cash flow</p>
                        <p
                            className="font-tabular mt-1 text-3xl font-medium md:text-4xl"
                            style={{ color: cashFlowPositive ? 'var(--color-positive)' : 'var(--color-ballast)' }}
                        >
                            {cashFlowPositive ? '↑' : '↓'} {formatCurrencyWhole(Math.abs(summary.netCashFlow))}
                        </p>
                    </div>
                    <div className="col-span-2 md:col-span-1">
                        <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Savings rate</p>
                        <p className="font-tabular mt-1 text-3xl font-medium md:text-4xl">{summary.savingsRate}%</p>
                    </div>
                </div>

                <div className="border-border border-t" />

                {/* Signature allocation visual */}
                <KeelLine totalBalance={summary.totalBalance} goals={activeGoals} />

                <div className="border-border border-t" />

                {/* Supporting lanes */}
                <div className="grid gap-8 lg:grid-cols-5">
                    <div className="space-y-8 lg:col-span-3">
                        <IncomeExpenseTrendChart data={trend} />
                        <SpendingByCategoryChart data={spendingByCategory} />
                    </div>
                    <div className="space-y-8 lg:col-span-2">
                        <HealthScoreCard healthScore={healthScore} />

                        {activeGoals.length > 0 && (
                            <div>
                                <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Next milestone</h2>
                                <div className="mt-2">
                                    <GoalProgressCard goal={nearestGoal(activeGoals)} />
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <div className="border-border border-t" />

                {/* Recent activity */}
                <div className="grid gap-8 lg:grid-cols-2">
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Insights</h2>
                            <Button variant="ghost" size="sm" asChild>
                                <Link href={route('insights.index')}>View all</Link>
                            </Button>
                        </div>
                        <div className="space-y-2">
                            {insights.slice(0, 3).map((insight) => (
                                <InsightCard key={insight.id} insight={insight} />
                            ))}
                            {insights.length === 0 && (
                                <p className="text-muted-foreground text-sm">
                                    No insights yet — they'll show up here once we spot a pattern worth flagging.
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Goals</h2>
                            <Button variant="ghost" size="sm" asChild>
                                <Link href={route('goals.index')}>View all</Link>
                            </Button>
                        </div>
                        {goals.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No goals yet — add one to start allocating.</p>
                        ) : (
                            <div className="space-y-2">
                                {goals.slice(0, 2).map((goal) => (
                                    <GoalProgressCard key={goal.id} goal={goal} />
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function nearestGoal(goals: GoalSummary[]): GoalSummary {
    return [...goals].sort((a, b) => {
        const aRemaining = Number(a.target_amount) - Number(a.current_amount);
        const bRemaining = Number(b.target_amount) - Number(b.current_amount);
        return aRemaining - bRemaining;
    })[0];
}
