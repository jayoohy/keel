import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface HealthScore {
    score: number;
    savings_rate: string;
    emergency_fund_coverage: string;
    spending_stability: string;
    income_consistency: string;
}

function scoreColor(score: number): string {
    if (score >= 70) return 'text-green-600';
    if (score >= 40) return 'text-yellow-600';
    return 'text-red-600';
}

export function HealthScoreCard({ healthScore }: { healthScore: HealthScore | null }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-sm font-normal">Financial health score</CardTitle>
            </CardHeader>
            <CardContent>
                {healthScore ? (
                    <>
                        <p className={`text-3xl font-bold ${scoreColor(healthScore.score)}`}>{healthScore.score}/100</p>
                        <dl className="text-muted-foreground mt-2 grid grid-cols-2 gap-1 text-xs">
                            <dt>Savings rate</dt>
                            <dd className="text-right">{healthScore.savings_rate}%</dd>
                            <dt>Emergency fund</dt>
                            <dd className="text-right">{healthScore.emergency_fund_coverage}%</dd>
                            <dt>Spending stability</dt>
                            <dd className="text-right">{healthScore.spending_stability}%</dd>
                            <dt>Income consistency</dt>
                            <dd className="text-right">{healthScore.income_consistency}%</dd>
                        </dl>
                    </>
                ) : (
                    <p className="text-muted-foreground text-sm">Not computed yet — check back after the next scheduled run.</p>
                )}
            </CardContent>
        </Card>
    );
}
