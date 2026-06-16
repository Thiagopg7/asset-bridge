import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Branch } from '@/types';

const UF_OPTIONS = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
    'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
    'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
];

type BranchFormErrors = Partial<Record<keyof Branch, string>>;

type Props = {
    errors: BranchFormErrors;
    processing: boolean;
    defaultValues?: Partial<Branch>;
    submitLabel: string;
};

export default function BranchForm({ errors, processing, defaultValues, submitLabel }: Props) {
    return (
        <div className="space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="name">Nome</Label>
                <Input
                    id="name"
                    name="name"
                    defaultValue={defaultValues?.name ?? ''}
                    placeholder="Ex: Filial São Paulo"
                    required
                />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="code">Código</Label>
                <Input
                    id="code"
                    name="code"
                    defaultValue={defaultValues?.code ?? ''}
                    placeholder="Ex: FIL-SP01"
                    required
                />
                <InputError message={errors.code} />
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="grid gap-2">
                    <Label htmlFor="city">Cidade</Label>
                    <Input
                        id="city"
                        name="city"
                        defaultValue={defaultValues?.city ?? ''}
                        placeholder="Ex: São Paulo"
                    />
                    <InputError message={errors.city} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="state">UF</Label>
                    <Select name="state" defaultValue={defaultValues?.state ?? ''}>
                        <SelectTrigger id="state">
                            <SelectValue placeholder="Selecione" />
                        </SelectTrigger>
                        <SelectContent>
                            {UF_OPTIONS.map((uf) => (
                                <SelectItem key={uf} value={uf}>
                                    {uf}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.state} />
                </div>
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
                <Label htmlFor="active">Filial ativa</Label>
            </div>

            <div className="flex items-center gap-4">
                <Button disabled={processing}>{submitLabel}</Button>
            </div>
        </div>
    );
}
