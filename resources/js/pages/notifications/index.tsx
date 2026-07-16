import { Head, Link, router } from '@inertiajs/react';

import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Notifications', href: '/notifications' }];

interface NotificationItem {
    id: string;
    data: { message: string };
    read_at: string | null;
    created_at: string;
}

interface PaginatedNotifications {
    data: NotificationItem[];
    links: { url: string | null; label: string; active: boolean }[];
}

export default function NotificationsIndex({ notifications }: { notifications: PaginatedNotifications }) {
    const markRead = (id: string) => {
        router.patch(route('notifications.update', id));
    };

    const markAllRead = () => {
        router.post(route('notifications.mark-all-read'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notifications" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Notifications" />
                    <Button variant="outline" size="sm" onClick={markAllRead}>
                        Mark all read
                    </Button>
                </div>

                <div className="space-y-2">
                    {notifications.data.map((notification) => (
                        <Card key={notification.id} className="cursor-pointer" onClick={() => markRead(notification.id)}>
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between gap-2">
                                    <p className="text-sm">{notification.data.message}</p>
                                    {notification.read_at && <span className="text-muted-foreground shrink-0 text-xs">Read</span>}
                                </div>
                                <p className="text-muted-foreground text-xs">{new Date(notification.created_at).toLocaleString()}</p>
                            </CardContent>
                        </Card>
                    ))}

                    {notifications.data.length === 0 && <p className="text-muted-foreground text-sm">No notifications yet.</p>}
                </div>

                <div className="flex flex-wrap gap-2">
                    {notifications.links.map((link, index) =>
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
