import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { ClipboardDocumentListIcon } from '@heroicons/react/24/outline';

export default function ActivitiesIndex({ activities }) {
    return (
        <AppLayout>
            <Head title="Activities" />

            <PageHeader
                title="Activities"
                description="View all activities across your leads"
            />

            <Card>
                <EmptyState
                    icon={ClipboardDocumentListIcon}
                    title="Activities page coming soon"
                    description="This page will show all activities across your leads."
                />
            </Card>
        </AppLayout>
    );
}

