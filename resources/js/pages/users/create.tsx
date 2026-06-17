import { Form, Head } from '@inertiajs/react';
import UserController from '@/actions/App/Http/Controllers/UserController';
import Heading from '@/components/heading';
import type { Branch } from '@/types';
import UserForm from './partials/user-form';

type Props = {
    branches: Pick<Branch, 'id' | 'name'>[];
    roles: string[];
};

export default function UserCreate({ branches, roles }: Props) {
    return (
        <>
            <Head title="Novo usuário" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    variant="small"
                    title="Novo usuário"
                    description="Preencha os dados para cadastrar um novo usuário"
                />

                <Form
                    {...UserController.store.form()}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <UserForm
                            errors={errors}
                            processing={processing}
                            branches={branches}
                            roles={roles}
                            submitLabel="Criar usuário"
                        />
                    )}
                </Form>
            </div>
        </>
    );
}

UserCreate.layout = {
    breadcrumbs: [
        { title: 'Usuários', href: UserController.index.url() },
        { title: 'Novo usuário', href: UserController.create.url() },
    ],
};
