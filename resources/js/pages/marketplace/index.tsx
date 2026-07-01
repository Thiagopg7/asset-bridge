import { Head, Link, useForm } from '@inertiajs/react';
import { PackageOpen } from 'lucide-react';
import { useState } from 'react';
import MarketplaceController from '@/actions/App/Http/Controllers/MarketplaceController';
import TransferController from '@/actions/App/Http/Controllers/TransferController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ASSET_UNIT_LABELS } from '@/types';
import type { MarketplaceNeed, MarketplaceOffer, Paginated } from '@/types';

type Props = {
    offers: Paginated<MarketplaceOffer>;
    needs: Paginated<MarketplaceNeed>;
    canRequest: boolean;
};

function PaginationBar<T>({
    page,
    label,
}: {
    page: Paginated<T>;
    label: string;
}) {
    if (page.last_page <= 1) {
        return null;
    }

    return (
        <div className="flex items-center justify-between text-sm text-muted-foreground">
            <span>
                Exibindo {page.from}–{page.to} de {page.total} {label}
            </span>
            <div className="flex gap-1">
                {page.links.map((link, i) => (
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
                            <span
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        )}
                    </Button>
                ))}
            </div>
        </div>
    );
}

export default function MarketplaceIndex({ offers, needs, canRequest }: Props) {
    const [requesting, setRequesting] = useState<MarketplaceOffer | null>(null);
    const { data, setData, post, processing, errors, reset } = useForm<{
        quantity: number;
        notes: string;
    }>({
        quantity: 1,
        notes: '',
    });

    function openRequest(offer: MarketplaceOffer) {
        reset();
        setData('quantity', 1);
        setRequesting(offer);
    }

    function submit() {
        if (!requesting) {
            return;
        }

        post(TransferController.store.url(requesting.id), {
            preserveScroll: true,
            onSuccess: () => setRequesting(null),
        });
    }

    return (
        <>
            <Head title="Marketplace" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Marketplace"
                    description="Ofertas e necessidades de ativos entre filiais"
                />

                <h2 className="text-sm font-semibold text-foreground">
                    Ofertas de excesso disponíveis
                </h2>

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Ativo</TableHead>
                                <TableHead>Filial de origem</TableHead>
                                <TableHead>Disponível</TableHead>
                                <TableHead>Observações</TableHead>
                                <TableHead className="w-[120px]" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {offers.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhuma oferta disponível no momento.
                                    </TableCell>
                                </TableRow>
                            )}
                            {offers.data.map((offer) => (
                                <TableRow key={offer.id}>
                                    <TableCell className="font-medium">
                                        {offer.asset_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {offer.branch_name}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {offer.available_quantity}{' '}
                                            {ASSET_UNIT_LABELS[offer.unit]}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="max-w-xs truncate text-muted-foreground">
                                        {offer.notes ?? '—'}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex justify-end">
                                            {canRequest && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() =>
                                                        openRequest(offer)
                                                    }
                                                >
                                                    <PackageOpen className="mr-2 h-4 w-4" />
                                                    Solicitar
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <PaginationBar page={offers} label="ofertas" />

                <h2 className="pt-2 text-sm font-semibold text-foreground">
                    Necessidades de outras filiais
                </h2>

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Ativo</TableHead>
                                <TableHead>Filial</TableHead>
                                <TableHead>Quantidade</TableHead>
                                <TableHead>Observações</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {needs.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={4}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhuma necessidade registrada por
                                        outras filiais.
                                    </TableCell>
                                </TableRow>
                            )}
                            {needs.data.map((need) => (
                                <TableRow key={need.id}>
                                    <TableCell className="font-medium">
                                        {need.asset_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {need.branch_name}
                                    </TableCell>
                                    <TableCell>
                                        {need.quantity}{' '}
                                        {ASSET_UNIT_LABELS[need.unit]}
                                    </TableCell>
                                    <TableCell className="max-w-xs truncate text-muted-foreground">
                                        {need.notes ?? '—'}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <PaginationBar page={needs} label="necessidades" />
            </div>

            <Dialog
                open={!!requesting}
                onOpenChange={(open) => !open && setRequesting(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Solicitar transferência</DialogTitle>
                        <DialogDescription>
                            Oferta de{' '}
                            <span className="font-semibold">
                                {requesting?.asset_name}
                            </span>{' '}
                            da filial {requesting?.branch_name} (disponível:{' '}
                            {requesting?.available_quantity}).
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <Label htmlFor="quantity">Quantidade</Label>
                            <Input
                                id="quantity"
                                type="number"
                                min={1}
                                max={requesting?.available_quantity}
                                value={data.quantity === 0 ? '' : data.quantity}
                                onChange={(e) =>
                                    setData(
                                        'quantity',
                                        e.target.value === ''
                                            ? 0
                                            : Number(e.target.value),
                                    )
                                }
                            />
                            <InputError message={errors.quantity} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="notes">Observações</Label>
                            <Input
                                id="notes"
                                value={data.notes}
                                onChange={(e) =>
                                    setData('notes', e.target.value)
                                }
                                placeholder="Detalhes opcionais"
                            />
                            <InputError message={errors.notes} />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setRequesting(null)}
                            disabled={processing}
                        >
                            Cancelar
                        </Button>
                        <Button onClick={submit} disabled={processing}>
                            Enviar solicitação
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

MarketplaceIndex.layout = {
    breadcrumbs: [
        { title: 'Marketplace', href: MarketplaceController.index.url() },
    ],
};
