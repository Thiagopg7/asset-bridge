import { Form, Head } from '@inertiajs/react';
import BranchController from '@/actions/App/Http/Controllers/BranchController';
import Heading from '@/components/heading';
import BranchForm from './partials/branch-form';

export default function BranchCreate() {
    return (
        <>
            <Head title="Nova filial" />

            <div className="px-4 py-6 space-y-6">
                <Heading
                    variant="small"
                    title="Nova filial"
                    description="Preencha os dados para cadastrar uma nova filial"
                />

                <Form
                    {...BranchController.store.form()}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <BranchForm
                            errors={errors}
                            processing={processing}
                            submitLabel="Criar filial"
                        />
                    )}
                </Form>
            </div>
        </>
    );
}

BranchCreate.layout = {
    breadcrumbs: [
        { title: 'Filiais', href: BranchController.index.url() },
        { title: 'Nova filial', href: BranchController.create.url() },
    ],
};
