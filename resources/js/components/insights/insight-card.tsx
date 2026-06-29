import { router } from '@inertiajs/react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

export interface InsightItem {
    id: number;
    type: 'categorization' | 'spending' | 'goal_progress' | 'anomaly';
    title: string;
    message: string;
    is_read: boolean;
    created_at: string;
}

const typeVariant: Record<InsightItem['type'], 'default' | 'secondary' | 'destructive'> = {
    categorization: 'secondary',
    spending: 'default',
    goal_progress: 'default',
    anomaly: 'destructive',
};

export function InsightCard({ insight }: { insight: InsightItem }) {
    const markRead = () => {
        if (!insight.is_read) {
            router.patch(route('insights.update', insight.id));
        }
    };

    const dismiss = () => {
        router.delete(route('insights.destroy', insight.id));
    };

    return (
        <Card className={insight.is_read ? 'opacity-70' : undefined} onMouseEnter={markRead}>
            <CardContent className="flex items-start justify-between gap-4 p-4">
                <div className="space-y-1">
                    <div className="flex items-center gap-2">
                        <Badge variant={typeVariant[insight.type]}>{insight.type.replace('_', ' ')}</Badge>
                        <p className="text-sm font-medium">{insight.title}</p>
                    </div>
                    <p className="text-muted-foreground text-sm">{insight.message}</p>
                    <p className="text-muted-foreground text-xs">{new Date(insight.created_at).toLocaleString()}</p>
                </div>
                <Button variant="ghost" size="sm" onClick={dismiss}>
                    Dismiss
                </Button>
            </CardContent>
        </Card>
    );
}
