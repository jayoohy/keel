interface HealthScore {
    score: number;
    savings_rate: string;
    emergency_fund_coverage: string;
    spending_stability: string;
    income_consistency: string;
}

function verdict(score: number): { label: string; color: string } {
    if (score >= 80) return { label: 'Steady course', color: 'var(--color-positive)' };
    if (score >= 60) return { label: 'Holding steady', color: 'var(--color-depth)' };
    if (score >= 40) return { label: 'Drifting', color: 'var(--color-brass)' };
    return { label: 'Off course', color: 'var(--color-ballast)' };
}

const FACTORS: { key: keyof Omit<HealthScore, 'score'>; label: string }[] = [
    { key: 'savings_rate', label: 'Savings consistency' },
    { key: 'emergency_fund_coverage', label: 'Emergency buffer' },
    { key: 'spending_stability', label: 'Spending volatility' },
    { key: 'income_consistency', label: 'Income consistency' },
];

export function HealthScoreCard({ healthScore }: { healthScore: HealthScore | null }) {
    return (
        <div className="flex h-full flex-col">
            <h2 className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Financial health</h2>

            {healthScore ? (
                <>
                    <div className="mt-2 flex items-baseline gap-3">
                        <span className="font-display text-5xl leading-none font-medium">{healthScore.score}</span>
                        <span className="text-sm font-medium" style={{ color: verdict(healthScore.score).color }}>
                            {verdict(healthScore.score).label}
                        </span>
                    </div>

                    <dl className="mt-5 space-y-3">
                        {FACTORS.map(({ key, label }) => {
                            const value = Math.max(0, Math.min(100, Number(healthScore[key])));
                            return (
                                <div key={key}>
                                    <div className="mb-1 flex items-center justify-between text-xs">
                                        <dt className="text-muted-foreground">{label}</dt>
                                        <dd className="font-tabular">{value}%</dd>
                                    </div>
                                    <div className="bg-border relative h-px w-full">
                                        <div className="bg-foreground absolute top-1/2 h-2 w-0.5 -translate-y-1/2" style={{ left: `${value}%` }} />
                                    </div>
                                </div>
                            );
                        })}
                    </dl>
                </>
            ) : (
                <p className="text-muted-foreground mt-3 text-sm">Not computed yet — check back after the next scheduled run.</p>
            )}
        </div>
    );
}
