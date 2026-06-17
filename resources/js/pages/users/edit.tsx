import { Form, Head } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/UserController';
import Heading from '@/components/heading';
import type { Branch, UserFormData } from '@/types';
import UserForm from './partials/user-form';

type Props = {
    user: UserFormData;
    branches: Pick<Branch, 'id' | 'name'>[];
    roles: string[];
};

export default function UserEdit({ user, branches, roles }: Props) {
    return (
        <>
            <Head title="Editar usuário" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    variant="small"
                    title="Editar usuário"
                    description={`Atualize os dados de ${user.name}`}
                />

                <Form
                    {...UserController.update.form(user.id)}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <UserForm
                            errors={errors}
                            processing={processing}
                            defaultValues={user}
                            branches={branches}
                            roles={roles}
                            submitLabel="Salvar alterações"
                            isEdit
                        />
                    )}
                </Form>
            </div>
        </>
    );
}

UserEdit.layout = {
    breadcrumbs: [
        { title: 'Usuários', href: UserController.index.url() },
        { title: 'Editar usuário', href: '#' },
    ],
};
