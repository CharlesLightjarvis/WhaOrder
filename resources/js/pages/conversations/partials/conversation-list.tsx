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

    const prevUrl = conversations.links[0]?.url;
    const nextUrl = conversations.links[conversations.links.length - 1]?.url;

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={conversations.data} />

            {conversations.last_page > 1 && (
                <div className="flex items-center justify-end gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        disabled={!prevUrl}
                        onClick={() => prevUrl && router.visit(prevUrl)}
                    >
                        Précédent
                    </Button>
                    <span className="text-sm text-muted-foreground">
                        Page {conversations.current_page} /{' '}
                        {conversations.last_page}
                    </span>
                    <Button
                        variant="outline"
                        size="sm"
                        disabled={!nextUrl}
                        onClick={() => nextUrl && router.visit(nextUrl)}
                    >
                        Suivant
                    </Button>
                </div>
            )}
        </div>
    );
}
