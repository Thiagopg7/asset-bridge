import { Head } from '@inertiajs/react';
import { CheckIcon } from 'lucide-react';
import RoleController from '@/actions/App/Http/Controllers/RoleController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const ROLE_LABELS: Record<string, string> = {
    admin: 'Admin',
    diretor: 'Diretor',
    gerente: 'Gerente',
    colaborador: 'Colaborador',
};

const PERMISSION_LABELS: Record<string, string> = {
    'branches.view': 'Visualizar filiais',
    'branches.manage': 'Gerenciar filiais',
    'users.view': 'Visualizar usuários',
    'users.manage': 'Gerenciar usuários',
    'roles.assign': 'Atribuir cargos',
    'requests.approve': 'Aprovar solicitações',
    'transfers.authorize': 'Autorizar transferências',
    'dispatch.execute': 'Executar expedição',
};

type RoleData = {
    name: string;
    permissions: string[];
};

type Props = {
    roles: RoleData[];
    permissions: string[];
};

export default function RolesIndex({ roles, permissions }: Props) {
    return (
        <>
            <Head title="Cargos e Permissões" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Cargos e Permissões"
                    description="Matriz de permissões por cargo (somente leitura)"
                />

                <div className="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-[220px]">
                                    Permissão
                                </TableHead>
                                {roles.map((role) => (
                                    <TableHead
                                        key={role.name}
                                        className="text-center"
                                    >
                                        <Badge variant="outline">
                                            {ROLE_LABELS[role.name] ??
                                                role.name}
                                        </Badge>
                                    </TableHead>
                                ))}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {permissions.map((permission) => (
                                <TableRow key={permission}>
                                    <TableCell className="font-medium">
                                        {PERMISSION_LABELS[permission] ??
                                            permission}
                                        <span className="ml-2 font-mono text-xs text-muted-foreground">
                                            {permission}
                                        </span>
                                    </TableCell>
                                    {roles.map((role) => (
                                        <TableCell
                                            key={role.name}
                                            className="text-center"
                                        >
                                            {role.permissions.includes(
                                                permission,
                                            ) ? (
                                                <CheckIcon className="mx-auto h-4 w-4 text-green-600" />
                                            ) : (
                                                <span className="text-muted-foreground">
                                                    —
                                                </span>
                                            )}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </>
    );
}

RolesIndex.layout = {
    breadcrumbs: [
        { title: 'Cargos e Permissões', href: RoleController.index.url() },
    ],
};
