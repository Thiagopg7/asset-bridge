import { Head, Link, router } from '@inertiajs/react';
import { PackageCheck, Send } from 'lucide-react';
import ShipmentController from '@/actions/App/Http/Controllers/ShipmentController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ASSET_UNIT_LABELS } from '@/types';
import type { Paginated, ShipmentListItem, ShipmentStatus } from '@/types';

type Props = {
    shipments: Paginated<ShipmentListItem>;
};

const STATUS_VARIANTS: Record<
    ShipmentStatus,
    'default' | 'secondary' | 'outline'
> = {
    ready: 'outline',
    in_transit: 'secondary',
    received: 'default',
};

export default function ShipmentsIndex({ shipments }: Props) {
    function dispatch(shipment: ShipmentListItem) {
        router.patch(
            ShipmentController.dispatch.url(shipment.id),
            {},
            { preserveScroll: true },
        );
    }

    function receive(shipment: ShipmentListItem) {
        router.patch(
            ShipmentController.receive.url(shipment.id),
            {},
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title="Expedição" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Expedição"
                    description="Fila de envios entre filiais"
                />

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Ativo</TableHead>
                                <TableHead>Origem</TableHead>
                                <TableHead>Destino</TableHead>
                                <TableHead>Quantidade</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="w-[160px]" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {shipments.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={6}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhuma expedição na fila.
                                    </TableCell>
                                </TableRow>
                            )}
                            {shipments.data.map((shipment) => (
                                <TableRow key={shipment.id}>
                                    <TableCell className="font-medium">
                                        {shipment.asset_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {shipment.origin_branch_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {shipment.destination_branch_name}
                                    </TableCell>
                                    <TableCell>
                                        {shipment.quantity}{' '}
                                        {ASSET_UNIT_LABELS[shipment.unit]}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                STATUS_VARIANTS[shipment.status]
                                            }
                                        >
                                            {shipment.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-2">
                                            {shipment.can_dispatch && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() =>
                                                        dispatch(shipment)
                                                    }
                                                >
                                                    <Send className="mr-2 h-4 w-4" />
                                                    Enviar
                                                </Button>
                                            )}
                                            {shipment.can_receive && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() =>
                                                        receive(shipment)
                                                    }
                                                >
                                                    <PackageCheck className="mr-2 h-4 w-4" />
                                                    Confirmar recebimento
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {shipments.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>
                            Exibindo {shipments.from}–{shipments.to} de{' '}
                            {shipments.total} expedições
                        </span>
                        <div className="flex gap-1">
                            {shipments.links.map((link, i) => (
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
        </>
    );
}

ShipmentsIndex.layout = {
    breadcrumbs: [{ title: 'Expedição', href: ShipmentController.index.url() }],
};
