import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Preferences',
        href: '/settings/preferences',
    },
];

interface PreferencesProps {
    currency: string;
    notificationTypes: Record<string, string>;
    notificationPreferences: Record<string, boolean>;
    status?: string;
}

export default function Preferences({ currency, notificationTypes, notificationPreferences, status }: PreferencesProps) {
    const { data, setData, put, processing, recentlySuccessful } = useForm({
        notification_preferences: notificationPreferences,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('preferences.update'), { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Preferences" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Currency" description="The currency used to display amounts across your account" />
                    <p className="text-muted-foreground text-sm">{currency} — additional currencies aren't supported yet.</p>

                    <HeadingSmall title="Notifications" description="Choose which notifications you want to receive" />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="space-y-4">
                            {Object.entries(notificationTypes).map(([key, label]) => (
                                <div key={key} className="flex items-center gap-3">
                                    <Checkbox
                                        id={key}
                                        checked={data.notification_preferences[key] ?? true}
                                        onCheckedChange={(checked) =>
                                            setData('notification_preferences', {
                                                ...data.notification_preferences,
                                                [key]: checked === true,
                                            })
                                        }
                                    />
                                    <Label htmlFor={key}>{label}</Label>
                                </div>
                            ))}
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                Save preferences
                            </Button>

                            {(recentlySuccessful || status === 'preferences-updated') && (
                                <p className="text-sm text-neutral-600">Saved</p>
                            )}
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
