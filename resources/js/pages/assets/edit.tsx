import { Form, Head } from '@inertiajs/react';
import AssetController from '@/actions/App/Http/Controllers/AssetController';
import Heading from '@/components/heading';
import type { Asset, AssetUnit } from '@/types';
import AssetForm from './partials/asset-form';

type Props = {
    asset: Asset;
    units: AssetUnit[];
};

export default function AssetEdit({ asset, units }: Props) {
    return (
        <>
            <Head title="Editar ativo" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    variant="small"
                    title="Editar ativo"
                    description={`Atualize os dados de ${asset.name}`}
                />

                <Form
                    {...AssetController.update.form(asset.id)}
                    options={{ preserveScroll: true }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <AssetForm
                            errors={errors}
                            processing={processing}
                            defaultValues={asset}
                            units={units}
                            submitLabel="Salvar alterações"
                        />
                    )}
                </Form>
            </div>
        </>
    );
}

AssetEdit.layout = {
    breadcrumbs: [
        { title: 'Ativos', href: AssetController.index.url() },
        { title: 'Editar ativo', href: '#' },
    ],
};
