import { Head, Link, router } from '@inertiajs/react';
import { CheckIcon, Trash2Icon, XIcon } from 'lucide-react';
import { useState } from 'react';
import TransferController from '@/actions/App/Http/Controllers/TransferController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ASSET_UNIT_LABELS } from '@/types';
import type { Paginated, TransferListItem, TransferStatus } from '@/types';

type Props = {
    transfers: Paginated<TransferListItem>;
};

const STATUS_VARIANTS: Record<
    TransferStatus,
    'default' | 'secondary' | 'destructive'
> = {
    pending: 'secondary',
    authorized: 'default',
    rejected: 'destructive',
};

export default function TransfersIndex({ transfers }: Props) {
    const [deleting, setDeleting] = useState<TransferListItem | null>(null);
    const [processing, setProcessing] = useState(false);

    function review(
        transfer: TransferListItem,
        action: 'authorize' | 'reject',
    ) {
        const url =
            action === 'authorize'
                ? TransferController.authorizeTransfer.url(transfer.id)
                : TransferController.reject.url(transfer.id);

        router.patch(url, {}, { preserveScroll: true });
    }

    function handleDelete() {
        if (!deleting) {
            return;
        }

        setProcessing(true);
        router.delete(TransferController.destroy.url(deleting.id), {
            preserveScroll: true,
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    }

    return (
        <>
            <Head title="Transferências" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Transferências"
                    description="Pedidos de transferência entre filiais"
                />

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Ativo</TableHead>
                                <TableHead>Origem</TableHead>
                                <TableHead>Destino</TableHead>
                                <TableHead>Quantidade</TableHead>
                                <TableHead>Solicitante</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="w-[140px]" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {transfers.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={7}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhuma transferência encontrada.
                                    </TableCell>
                                </TableRow>
                            )}
                            {transfers.data.map((transfer) => (
                                <TableRow key={transfer.id}>
                                    <TableCell className="font-medium">
                                        {transfer.asset_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {transfer.offer_branch_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {transfer.branch_name}
                                    </TableCell>
                                    <TableCell>
                                        {transfer.quantity}{' '}
                                        {ASSET_UNIT_LABELS[transfer.unit]}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {transfer.user_name}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                STATUS_VARIANTS[transfer.status]
                                            }
                                        >
                                            {transfer.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-2">
                                            {transfer.can_review && (
                                                <>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-emerald-600 hover:text-emerald-600"
                                                        onClick={() =>
                                                            review(
                                                                transfer,
                                                                'authorize',
                                                            )
                                                        }
                                                    >
                                                        <CheckIcon className="h-4 w-4" />
                                                        <span className="sr-only">
                                                            Autorizar
                                                        </span>
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-destructive hover:text-destructive"
                                                        onClick={() =>
                                                            review(
                                                                transfer,
                                                                'reject',
                                                            )
                                                        }
                                                    >
                                                        <XIcon className="h-4 w-4" />
                                                        <span className="sr-only">
                                                            Rejeitar
                                                        </span>
                                                    </Button>
                                                </>
                                            )}
                                            {transfer.can_delete && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() =>
                                                        setDeleting(transfer)
                                                    }
                                                >
                                                    <Trash2Icon className="h-4 w-4" />
                                                    <span className="sr-only">
                                                        Cancelar
                                                    </span>
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {transfers.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            Exibindo {transfers.from}–{transfers.to} de{' '}
                            {transfers.total} transferências
                        </span>
                        <div className="flex gap-1">
                            {transfers.links.map((link, i) => (
                                <Button
                                    key={i}
                                    variant={
                                        link.active ? 'default' : 'outline'
                                    }
                                    size="sm"
                                    disabled={!link.url}
                                    asChild={!!link.url}
                                >
                                    {link.url ? (
                                        <Link
                                            href={link.url}
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    ) : (
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    )}
                                </Button>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            <Dialog
                open={!!deleting}
                onOpenChange={(open) => !open && setDeleting(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cancelar transferência</DialogTitle>
                        <DialogDescription>
                            Tem certeza que deseja cancelar a solicitação de{' '}
                            <span className="font-semibold">
                                {deleting?.asset_name}
                            </span>
                            ? Esta ação não pode ser desfeita.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleting(null)}
                            disabled={processing}
                        >
                            Voltar
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={processing}
                        >
                            Cancelar transferência
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

TransfersIndex.layout = {
    breadcrumbs: [
        { title: 'Transferências', href: TransferController.index.url() },
    ],
};
