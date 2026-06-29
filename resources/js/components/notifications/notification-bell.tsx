import { Link, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePolling } from '@/hooks/use-polling';
import { type SharedData } from '@/types';

export function NotificationBell() {
    const { unreadNotificationsCount } = usePage<SharedData>().props;

    usePolling(['unreadNotificationsCount'], 30000);

    return (
        <Button variant="ghost" size="icon" asChild className="relative">
            <Link href={route('notifications.index')}>
                <Bell className="h-5 w-5" />
                {unreadNotificationsCount > 0 && (
                    <Badge variant="destructive" className="absolute -right-1 -top-1 h-5 min-w-5 justify-center rounded-full px-1 text-xs">
                        {unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount}
                    </Badge>
                )}
            </Link>
        </Button>
    );
}
