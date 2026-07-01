import { Head, Link, router } from '@inertiajs/react';
import {
    CheckIcon,
    PencilIcon,
    PlusIcon,
    Trash2Icon,
    XIcon,
} from 'lucide-react';
import { useState } from 'react';
import AssetRequestController from '@/actions/App/Http/Controllers/AssetRequestController';
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
import type {
    AssetRequestListItem,
    AssetRequestStatus,
    Paginated,
} from '@/types';

type Props = {
    requests: Paginated<AssetRequestListItem>;
    canCreate: boolean;
};

const STATUS_VARIANTS: Record<
    AssetRequestStatus,
    'default' | 'secondary' | 'destructive'
> = {
    pending: 'secondary',
    approved: 'default',
    rejected: 'destructive',
};

export default function AssetRequestsIndex({ requests, canCreate }: Props) {
    const [deleting, setDeleting] = useState<AssetRequestListItem | null>(null);
    const [processing, setProcessing] = useState(false);

    function review(
        request: AssetRequestListItem,
        action: 'approve' | 'reject',
    ) {
        const url =
            action === 'approve'
                ? AssetRequestController.approve.url(request.id)
                : AssetRequestController.reject.url(request.id);

        router.patch(url, {}, { preserveScroll: true });
    }

    function handleDelete() {
        if (!deleting) {
            return;
        }

        setProcessing(true);
        router.delete(AssetRequestController.destroy.url(deleting.id), {
            preserveScroll: true,
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    }

    return (
        <>
            <Head title="Solicitações" />

            <div className="space-y-6 px-4 py-6">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Solicitações"
                        description="Necessidades e ofertas de excesso das filiais"
                    />
                    {canCreate && (
                        <Button asChild size="sm">
                            <Link href={AssetRequestController.create.url()}>
                                <PlusIcon className="mr-2 h-4 w-4" />
                                Nova solicitação
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tipo</TableHead>
                                <TableHead>Ativo</TableHead>
                                <TableHead>Quantidade</TableHead>
                                <TableHead>Filial</TableHead>
                                <TableHead>Solicitante</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="w-[140px]" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {requests.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={7}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhuma solicitação encontrada.
                                    </TableCell>
                                </TableRow>
                            )}
                            {requests.data.map((request) => (
                                <TableRow key={request.id}>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                request.type === 'need'
                                                    ? 'outline'
                                                    : 'secondary'
                                            }
                                        >
                                            {request.type_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {request.asset_name}
                                    </TableCell>
                                    <TableCell>
                                        {request.quantity}{' '}
                                        {ASSET_UNIT_LABELS[request.unit]}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {request.branch_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {request.user_name}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                STATUS_VARIANTS[request.status]
                                            }
                                        >
                                            {request.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-2">
                                            {request.can_review && (
                                                <>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-emerald-600 hover:text-emerald-600"
                                                        onClick={() =>
                                                            review(
                                                                request,
                                                                'approve',
                                                            )
                                                        }
                                                    >
                                                        <CheckIcon className="h-4 w-4" />
                                                        <span className="sr-only">
                                                            Aprovar
                                                        </span>
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-destructive hover:text-destructive"
                                                        onClick={() =>
                                                            review(
                                                                request,
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
                                            {request.can_edit && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link
                                                        href={AssetRequestController.edit.url(
                                                            request.id,
                                                        )}
                                                    >
                                                        <PencilIcon className="h-4 w-4" />
                                                        <span className="sr-only">
                                                            Editar
                                                        </span>
                                                    </Link>
                                                </Button>
                                            )}
                                            {request.can_delete && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() =>
                                                        setDeleting(request)
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

                {requests.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            Exibindo {requests.from}–{requests.to} de{' '}
                            {requests.total} solicitações
                        </span>
                        <div className="flex gap-1">
                            {requests.links.map((link, i) => (
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
                        <DialogTitle>Cancelar solicitação</DialogTitle>
                        <DialogDescription>
                            Tem certeza que deseja cancelar esta solicitação de{' '}
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
                            Cancelar solicitação
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

AssetRequestsIndex.layout = {
    breadcrumbs: [
        { title: 'Solicitações', href: AssetRequestController.index.url() },
    ],
};
