import { Form, Head } from '@inertiajs/react';
import AssetRequestController from '@/actions/App/Http/Controllers/AssetRequestController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ASSET_UNIT_LABELS } from '@/types';
import type {
    AssetOption,
    AssetRequestType,
    AssetRequestTypeOption,
} from '@/types';

type EditableRequest = {
    id: number;
    asset_id: number;
    type: AssetRequestType;
    quantity: number;
    notes: string | null;
};

type Props = {
    assetRequest: EditableRequest;
    assets: AssetOption[];
    types: AssetRequestTypeOption[];
};

export default function AssetRequestEdit({
    assetRequest,
    assets,
    types,
}: Props) {
    return (
        <>
            <Head title="Editar solicitação" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    variant="small"
                    title="Editar solicitação"
                    description="Ajuste os dados enquanto a solicitação estiver pendente"
                />

                <Form
                    {...AssetRequestController.update.form(assetRequest.id)}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <div className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="type">Tipo</Label>
                                <Select
                                    name="type"
                                    defaultValue={assetRequest.type}
                                >
                                    <SelectTrigger id="type">
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {types.map((type) => (
                                            <SelectItem
                                                key={type.value}
                                                value={type.value}
                                            >
                                                {type.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.type} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="asset_id">Ativo</Label>
                                <Select
                                    name="asset_id"
                                    defaultValue={String(assetRequest.asset_id)}
                                >
                                    <SelectTrigger id="asset_id">
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {assets.map((asset) => (
                                            <SelectItem
                                                key={asset.id}
                                                value={String(asset.id)}
                                            >
                                                {asset.name} (
                                                {ASSET_UNIT_LABELS[asset.unit]})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.asset_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="quantity">Quantidade</Label>
                                <Input
                                    id="quantity"
                                    name="quantity"
                                    type="number"
                                    min={1}
                                    defaultValue={assetRequest.quantity}
                                    required
                                />
                                <InputError message={errors.quantity} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">Observações</Label>
                                <Input
                                    id="notes"
                                    name="notes"
                                    defaultValue={assetRequest.notes ?? ''}
                                    placeholder="Detalhes opcionais"
                                />
                                <InputError message={errors.notes} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Salvar alterações
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

AssetRequestEdit.layout = {
    breadcrumbs: [
        { title: 'Solicitações', href: AssetRequestController.index.url() },
        { title: 'Editar solicitação', href: '#' },
    ],
};
