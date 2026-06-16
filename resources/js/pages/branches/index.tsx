import { Head, Link, router } from '@inertiajs/react';
import { PencilIcon, PlusIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';
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
import BranchController from '@/actions/App/Http/Controllers/BranchController';
import type { Branch, Paginated } from '@/types';

type Props = {
    branches: Paginated<Branch>;
};

export default function BranchesIndex({ branches }: Props) {
    const [deleting, setDeleting] = useState<Branch | null>(null);
    const [processing, setProcessing] = useState(false);

    function handleDelete() {
        if (!deleting) return;
        setProcessing(true);
        router.delete(BranchController.destroy.url(deleting.id), {
            onFinish: () => {
                setProcessing(false);
                setDeleting(null);
            },
        });
    }

    return (
        <>
            <Head title="Filiais" />

            <div className="px-4 py-6 space-y-6">
                <div className="flex items-center justify-between">
                    <Heading title="Filiais" description="Gerencie as filiais da empresa" />
                    <Button asChild size="sm">
                        <Link href={BranchController.create.url()}>
                            <PlusIcon className="mr-2 h-4 w-4" />
                            Nova filial
                        </Link>
                    </Button>
                </div>

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nome</TableHead>
                                <TableHead>Código</TableHead>
                                <TableHead>Cidade</TableHead>
                                <TableHead>UF</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="w-[100px]" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {branches.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={6}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhuma filial cadastrada.
                                    </TableCell>
                                </TableRow>
                            )}
                            {branches.data.map((branch) => (
                                <TableRow key={branch.id}>
                                    <TableCell className="font-medium">
                                        {branch.name}
                                    </TableCell>
                                    <TableCell className="font-mono text-sm">
                                        {branch.code}
                                    </TableCell>
                                    <TableCell>{branch.city ?? '—'}</TableCell>
                                    <TableCell>{branch.state ?? '—'}</TableCell>
                                    <TableCell>
                                        {branch.active ? (
                                            <Badge variant="default">Ativa</Badge>
                                        ) : (
                                            <Badge variant="secondary">Inativa</Badge>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link href={BranchController.edit.url(branch.id)}>
                                                    <PencilIcon className="h-4 w-4" />
                                                    <span className="sr-only">Editar</span>
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => setDeleting(branch)}
                                            >
                                                <Trash2Icon className="h-4 w-4" />
                                                <span className="sr-only">Excluir</span>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {branches.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            Exibindo {branches.from}–{branches.to} de {branches.total} filiais
                        </span>
                        <div className="flex gap-1">
                            {branches.links.map((link, i) => (
                                <Button
                                    key={i}
                                    variant={link.active ? 'default' : 'outline'}
                                    size="sm"
                                    disabled={!link.url}
                                    asChild={!!link.url}
                                >
                                    {link.url ? (
                                        <Link
                                            href={link.url}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ) : (
                                        <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                    )}
                                </Button>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            <Dialog open={!!deleting} onOpenChange={(open) => !open && setDeleting(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Excluir filial</DialogTitle>
                        <DialogDescription>
                            Tem certeza que deseja excluir{' '}
                            <span className="font-semibold">{deleting?.name}</span>? Os
                            colaboradores vinculados perderão o vínculo com a filial. Esta ação
                            não pode ser desfeita.
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

BranchesIndex.layout = {
    breadcrumbs: [
        { title: 'Filiais', href: BranchController.index.url() },
    ],
};
