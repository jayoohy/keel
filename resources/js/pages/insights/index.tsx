import { Head, Link } from '@inertiajs/react';

import Heading from '@/components/heading';
import { InsightCard, type InsightItem } from '@/components/insights/insight-card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Insights', href: '/insights' }];

interface PaginatedInsights {
    data: InsightItem[];
    links: { url: string | null; label: string; active: boolean }[];
}

export default function InsightsIndex({ insights }: { insights: PaginatedInsights }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Insights" />

            <div className="space-y-6 p-4">
                <Heading title="Insights" description="Spending changes, goal progress, and unusual transactions" />

                <div className="space-y-2">
                    {insights.data.map((insight) => (
                        <InsightCard key={insight.id} insight={insight} />
                    ))}

                    {insights.data.length === 0 && <p className="text-muted-foreground text-sm">No insights yet.</p>}
                </div>

                <div className="flex gap-2">
                    {insights.links.map((link, index) =>
                        link.url ? (
                            <Link
                                key={index}
                                href={link.url}
                                className={`rounded px-3 py-1 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'bg-muted'}`}
                            >
                                {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                            </Link>
                        ) : null,
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
