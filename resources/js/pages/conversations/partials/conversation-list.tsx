import { useMemo } from 'react';
import { router } from '@inertiajs/react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types/pagination';
import type { Conversation } from '@/types/conversation';
import { createColumns } from './columns';

type Props = {
    conversations: Paginated<Conversation>;
};

export default function ConversationList({ conversations }: Props) {
    const columns = useMemo(() => createColumns(), []);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={conversations.data} />
        </div>
    );
}
