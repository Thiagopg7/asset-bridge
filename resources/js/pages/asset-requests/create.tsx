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
import type { AssetOption, AssetRequestTypeOption } from '@/types';

type Props = {
    assets: AssetOption[];
    types: AssetRequestTypeOption[];
};

export default function AssetRequestCreate({ assets, types }: Props) {
    return (
        <>
            <Head title="Nova solicitação" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    variant="small"
                    title="Nova solicitação"
                    description="Registre uma necessidade ou ofereça um excesso da sua filial"
                />

                <Form
                    {...AssetRequestController.store.form()}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <div className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="type">Tipo</Label>
                                <Select name="type">
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
                                <Select name="asset_id">
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
                                    defaultValue={1}
                                    required
                                />
                                <InputError message={errors.quantity} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">Observações</Label>
                                <Input
                                    id="notes"
                                    name="notes"
                                    placeholder="Detalhes opcionais"
                                />
                                <InputError message={errors.notes} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Criar solicitação
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

AssetRequestCreate.layout = {
    breadcrumbs: [
        { title: 'Solicitações', href: AssetRequestController.index.url() },
        {
            title: 'Nova solicitação',
            href: AssetRequestController.create.url(),
        },
    ],
};
