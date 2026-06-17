import { Head, Link, router, usePage } from '@inertiajs/react';
import { PencilIcon, PlusIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';
import AssetController from '@/actions/App/Http/Controllers/AssetController';
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
import type { Asset, Paginated } from '@/types';

type Props = {
    assets: Paginated<Asset>;
};

export default function AssetsIndex({ assets }: Props) {
    const { can } = usePage().props;
    const [deleting, setDeleting] = useState<Asset | null>(null);
    const [processing, setProcessing] = useState(false);

    function handleDelete() {
        if (!deleting) {
            return;
        }

        setProcessing(true);
        router.delete(AssetController.destroy.url(deleting.id), {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    }

    return (
        <>
            <Head title="Ativos" />

            <div className="space-y-6 px-4 py-6">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Ativos"
                        description="Catálogo global de ativos da empresa"
                    />
                    {can.manageAssets && (
                        <Button asChild size="sm">
                            <Link href={AssetController.create.url()}>
                                <PlusIcon className="mr-2 h-4 w-4" />
                                Novo ativo
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nome</TableHead>
                                <TableHead>Descrição</TableHead>
                                <TableHead>Unidade</TableHead>
                                <TableHead>Status</TableHead>
                                {can.manageAssets && (
                                    <TableHead className="w-[100px]" />
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {assets.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={can.manageAssets ? 5 : 4}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhum ativo cadastrado.
                                    </TableCell>
                                </TableRow>
                            )}
                            {assets.data.map((asset) => (
                                <TableRow key={asset.id}>
                                    <TableCell className="font-medium">
                                        {asset.name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {asset.description ?? '—'}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {ASSET_UNIT_LABELS[asset.unit]}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {asset.active ? (
                                            <Badge variant="default">
                                                Ativo
                                            </Badge>
                                        ) : (
                                            <Badge variant="secondary">
                                                Inativo
                                            </Badge>
                                        )}
                                    </TableCell>
                                    {can.manageAssets && (
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link
                                                        href={AssetController.edit.url(
                                                            asset.id,
                                                        )}
                                                    >
                                                        <PencilIcon className="h-4 w-4" />
                                                        <span className="sr-only">
                                                            Editar
                                                        </span>
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive"
                                                    onClick={() =>
                                                        setDeleting(asset)
                                                    }
                                                >
                                                    <Trash2Icon className="h-4 w-4" />
                                                    <span className="sr-only">
                                                        Excluir
                                                    </span>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    )}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {assets.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            Exibindo {assets.from}–{assets.to} de {assets.total}{' '}
                            ativos
                        </span>
                        <div className="flex gap-1">
                            {assets.links.map((link, i) => (
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
                        <DialogTitle>Excluir ativo</DialogTitle>
                        <DialogDescription>
                            Tem certeza que deseja excluir{' '}
                            <span className="font-semibold">
                                {deleting?.name}
                            </span>
                            ? Esta ação removerá o ativo do catálogo e de todos
                            os estoques. Não pode ser desfeita.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleting(null)}
                            disabled={processing}
                        >
                            Cancelar
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={processing}
                        >
                            Excluir
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

AssetsIndex.layout = {
    breadcrumbs: [{ title: 'Ativos', href: AssetController.index.url() }],
};
