import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import BranchController from '@/actions/App/Http/Controllers/BranchController';
import type { Branch } from '@/types';
import BranchForm from './partials/branch-form';

type Props = {
    branch: Branch;
};

export default function BranchEdit({ branch }: Props) {
    return (
        <>
            <Head title="Editar filial" />

            <div className="px-4 py-6 space-y-6">
                <Heading
                    variant="small"
                    title="Editar filial"
                    description={`Atualize os dados de ${branch.name}`}
                />

                <Form
                    {...BranchController.update.form(branch.id)}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <BranchForm
                            errors={errors}
                            processing={processing}
                            defaultValues={branch}
                            submitLabel="Salvar alterações"
                        />
                    )}
                </Form>
            </div>
        </>
    );
}

BranchEdit.layout = {
    breadcrumbs: [
        { title: 'Filiais', href: BranchController.index.url() },
        { title: 'Editar filial', href: '#' },
    ],
};
