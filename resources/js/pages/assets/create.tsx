import { Form, Head } from '@inertiajs/react';
import AssetController from '@/actions/App/Http/Controllers/AssetController';
import Heading from '@/components/heading';
import type { AssetUnit } from '@/types';
import AssetForm from './partials/asset-form';

type Props = {
    units: AssetUnit[];
};

export default function AssetCreate({ units }: Props) {
    return (
        <>
            <Head title="Novo ativo" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    variant="small"
                    title="Novo ativo"
                    description="Preencha os dados para cadastrar um novo ativo no catálogo"
                />

                <Form
                    {...AssetController.store.form()}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <AssetForm
                            errors={errors}
                            processing={processing}
                            units={units}
                            submitLabel="Criar ativo"
                        />
                    )}
                </Form>
            </div>
        </>
    );
}

AssetCreate.layout = {
    breadcrumbs: [
        { title: 'Ativos', href: AssetController.index.url() },
        { title: 'Novo ativo', href: AssetController.create.url() },
    ],
};
