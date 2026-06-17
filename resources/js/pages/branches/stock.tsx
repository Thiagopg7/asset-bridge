import { Head, router } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import { useState } from 'react';
import BranchController from '@/actions/App/Http/Controllers/BranchController';
import BranchStockController from '@/actions/App/Http/Controllers/BranchStockController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ASSET_UNIT_LABELS } from '@/types';
import type { StockEntry } from '@/types';

type BranchSummary = {
    id: number;
    name: string;
    code: string;
};

type Props = {
    branch: BranchSummary;
    stock: StockEntry[];
    canUpdate: boolean;
};

export default function BranchStock({ branch, stock, canUpdate }: Props) {
    const [quantities, setQuantities] = useState<Record<number, string>>(
        Object.fromEntries(stock.map((s) => [s.asset_id, String(s.quantity)])),
    );
    const [saving, setSaving] = useState<number | null>(null);

    function handleSave(assetId: number) {
        const quantity = parseInt(quantities[assetId] ?? '0', 10);

        if (isNaN(quantity) || quantity < 0) {
            return;
        }

        setSaving(assetId);
        router.patch(
            BranchStockController.update.url(branch.id, assetId),
            { quantity },
            {
                preserveScroll: true,
                onFinish: () => setSaving(null),
            },
        );
    }

    return (
        <>
            <Head title={`Estoque — ${branch.name}`} />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title={`Estoque — ${branch.name}`}
                    description={`Código: ${branch.code}`}
                />

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Ativo</TableHead>
                                <TableHead>Unidade</TableHead>
                                <TableHead className="w-[140px]">
                                    Quantidade
                                </TableHead>
                                {canUpdate && (
                                    <TableHead className="w-[80px]" />
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {stock.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={canUpdate ? 4 : 3}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        Nenhum ativo cadastrado no catálogo.
                                    </TableCell>
                                </TableRow>
                            )}
                            {stock.map((entry) => (
                                <TableRow key={entry.asset_id}>
                                    <TableCell className="font-medium">
                                        {entry.asset_name}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {ASSET_UNIT_LABELS[entry.unit]}
                                    </TableCell>
                                    <TableCell>
                                        {canUpdate ? (
                                            <Input
                                                type="number"
                                                min={0}
                                                value={
                                                    quantities[
                                                        entry.asset_id
                                                    ] ?? '0'
                                                }
                                                onChange={(e) =>
                                                    setQuantities((prev) => ({
                                                        ...prev,
                                                        [entry.asset_id]:
                                                            e.target.value,
                                                    }))
                                                }
                                                className="w-24"
                                            />
                                        ) : (
                                            <span>{entry.quantity}</span>
                                        )}
                                    </TableCell>
                                    {canUpdate && (
                                        <TableCell>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                disabled={
                                                    saving === entry.asset_id
                                                }
                                                onClick={() =>
                                                    handleSave(entry.asset_id)
                                                }
                                            >
                                                <SaveIcon className="h-4 w-4" />
                                                <span className="sr-only">
                                                    Salvar
                                                </span>
                                            </Button>
                                        </TableCell>
                                    )}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </>
    );
}

BranchStock.layout = {
    breadcrumbs: [
        { title: 'Filiais', href: BranchController.index.url() },
        { title: 'Estoque', href: '#' },
    ],
};
