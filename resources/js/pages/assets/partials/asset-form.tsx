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
import type { Asset, AssetUnit } from '@/types';

type AssetFormErrors = Partial<Record<keyof Asset, string>>;

type Props = {
    errors: AssetFormErrors;
    processing: boolean;
    defaultValues?: Partial<Asset>;
    units: AssetUnit[];
    submitLabel: string;
};

export default function AssetForm({
    errors,
    processing,
    defaultValues,
    units,
    submitLabel,
}: Props) {
    return (
        <div className="space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="name">Nome</Label>
                <Input
                    id="name"
                    name="name"
                    defaultValue={defaultValues?.name ?? ''}
                    placeholder="Ex: Cabo de Rede Cat6"
                    required
                />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Descrição</Label>
                <Input
                    id="description"
                    name="description"
                    defaultValue={defaultValues?.description ?? ''}
                    placeholder="Descrição opcional"
                />
                <InputError message={errors.description} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="unit">Unidade de medida</Label>
                <Select name="unit" defaultValue={defaultValues?.unit ?? ''}>
                    <SelectTrigger id="unit">
                        <SelectValue placeholder="Selecione" />
                    </SelectTrigger>
                    <SelectContent>
                        {units.map((unit) => (
                            <SelectItem key={unit} value={unit}>
                                {ASSET_UNIT_LABELS[unit]}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.unit} />
            </div>

            <div className="flex items-center gap-2">
                <input
                    id="active"
                    name="active"
                    type="checkbox"
                    defaultChecked={defaultValues?.active ?? true}
                    className="h-4 w-4 rounded border-input accent-primary"
                    value="1"
                />
                <Label htmlFor="active">Ativo no catálogo</Label>
            </div>

            <div className="flex items-center gap-4">
                <Button disabled={processing}>{submitLabel}</Button>
            </div>
        </div>
    );
}
