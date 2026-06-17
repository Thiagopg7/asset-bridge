import { useForm } from '@inertiajs/react';
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
import type { Branch, UserFormData } from '@/types';

type Props = {
    errors: Partial<Record<string, string>>;
    processing: boolean;
    defaultValues?: Partial<UserFormData>;
    branches: Pick<Branch, 'id' | 'name'>[];
    roles: string[];
    submitLabel: string;
    isEdit?: boolean;
};

const ROLE_LABELS: Record<string, string> = {
    admin: 'Admin',
    diretor: 'Diretor',
    gerente: 'Gerente',
    colaborador: 'Colaborador',
};

export default function UserForm({
    errors,
    processing,
    defaultValues,
    branches,
    roles,
    submitLabel,
    isEdit = false,
}: Props) {
    const NONE = '__none__';

    const { data, setData } = useForm({
        name: defaultValues?.name ?? '',
        email: defaultValues?.email ?? '',
        password: '',
        password_confirmation: '',
        role: defaultValues?.role ?? '',
        branch_id: defaultValues?.branch_id ? String(defaultValues.branch_id) : NONE,
    });

    return (
        <div className="space-y-5">
            <div className="space-y-2">
                <Label htmlFor="name">Nome</Label>
                <Input
                    id="name"
                    name="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Nome completo"
                    autoComplete="name"
                />
                {errors.name && (
                    <p className="text-sm text-destructive">{errors.name}</p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="email">E-mail</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    placeholder="email@exemplo.com"
                    autoComplete="email"
                />
                {errors.email && (
                    <p className="text-sm text-destructive">{errors.email}</p>
                )}
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label htmlFor="password">
                        {isEdit ? 'Nova senha' : 'Senha'}
                    </Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        placeholder={isEdit ? 'Deixe em branco para manter' : ''}
                        autoComplete="new-password"
                    />
                    {errors.password && (
                        <p className="text-sm text-destructive">
                            {errors.password}
                        </p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="password_confirmation">
                        Confirmar senha
                    </Label>
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        autoComplete="new-password"
                    />
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label htmlFor="role">Cargo</Label>
                    <Select
                        name="role"
                        value={data.role}
                        onValueChange={(v) => setData('role', v)}
                    >
                        <SelectTrigger id="role" className="w-full">
                            <SelectValue placeholder="Selecione um cargo" />
                        </SelectTrigger>
                        <SelectContent>
                            {roles.map((role) => (
                                <SelectItem key={role} value={role}>
                                    {ROLE_LABELS[role] ?? role}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {errors.role && (
                        <p className="text-sm text-destructive">{errors.role}</p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="branch_id">Filial</Label>
                    <input
                        type="hidden"
                        name="branch_id"
                        value={data.branch_id === NONE ? '' : data.branch_id}
                    />
                    <Select
                        value={data.branch_id}
                        onValueChange={(v) => setData('branch_id', v)}
                    >
                        <SelectTrigger id="branch_id" className="w-full">
                            <SelectValue placeholder="Sem filial" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={NONE}>Sem filial</SelectItem>
                            {branches.map((branch) => (
                                <SelectItem
                                    key={branch.id}
                                    value={String(branch.id)}
                                >
                                    {branch.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {errors.branch_id && (
                        <p className="text-sm text-destructive">
                            {errors.branch_id}
                        </p>
                    )}
                </div>
            </div>

            <div className="flex justify-end gap-3 pt-2">
                <Button type="submit" disabled={processing}>
                    {submitLabel}
                </Button>
            </div>
        </div>
    );
}
