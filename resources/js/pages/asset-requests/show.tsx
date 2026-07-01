import { Head, Link, router } from '@inertiajs/react';
import { CheckIcon, PencilIcon, XIcon } from 'lucide-react';
import AssetRequestController from '@/actions/App/Http/Controllers/AssetRequestController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ASSET_UNIT_LABELS } from '@/types';
import type { AssetRequestDetail, AssetRequestStatus } from '@/types';

type Props = {
    request: AssetRequestDetail;
};

const STATUS_VARIANTS: Record<
    AssetRequestStatus,
    'default' | 'secondary' | 'destructive'
> = {
    pending: 'secondary',
    approved: 'default',
    rejected: 'destructive',
};

function Field({ label, value }: { label: string; value: string }) {
    return (
        <div className="grid gap-1">
            <span className="text-xs text-muted-foreground">{label}</span>
            <span className="text-sm font-medium">{value}</span>
        </div>
    );
}

export default function AssetRequestShow({ request }: Props) {
    function review(action: 'approve' | 'reject') {
        const url =
            action === 'approve'
                ? AssetRequestController.approve.url(request.id)
                : AssetRequestController.reject.url(request.id);

        router.patch(url, {}, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Detalhes da solicitação" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Detalhes da solicitação"
                    description="Informações completas e ações de revisão"
                />

                <Card className="max-w-2xl">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>{request.asset_name}</CardTitle>
                        <div className="flex items-center gap-2">
                            <Badge
                                variant={
                                    request.type === 'need'
                                        ? 'outline'
                                        : 'secondary'
                                }
                            >
                                {request.type_label}
                            </Badge>
                            <Badge variant={STATUS_VARIANTS[request.status]}>
                                {request.status_label}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="grid grid-cols-2 gap-4">
                        <Field
                            label="Quantidade"
                            value={`${request.quantity} ${ASSET_UNIT_LABELS[request.unit]}`}
                        />
                        <Field label="Filial" value={request.branch_name} />
                        <Field label="Solicitante" value={request.user_name} />
                        <Field
                            label="Revisado por"
                            value={request.reviewer_name ?? '—'}
                        />
                        <div className="col-span-2">
                            <Field
                                label="Observações"
                                value={request.notes ?? '—'}
                            />
                        </div>
                    </CardContent>
                    <CardFooter className="flex items-center justify-between">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={AssetRequestController.index.url()}>
                                Voltar
                            </Link>
                        </Button>
                        <div className="flex items-center gap-2">
                            {request.can_edit && (
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        href={AssetRequestController.edit.url(
                                            request.id,
                                        )}
                                    >
                                        <PencilIcon className="mr-2 h-4 w-4" />
                                        Editar
                                    </Link>
                                </Button>
                            )}
                            {request.can_review && (
                                <>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        className="text-destructive hover:text-destructive"
                                        onClick={() => review('reject')}
                                    >
                                        <XIcon className="mr-2 h-4 w-4" />
                                        Rejeitar
                                    </Button>
                                    <Button
                                        size="sm"
                                        onClick={() => review('approve')}
                                    >
                                        <CheckIcon className="mr-2 h-4 w-4" />
                                        Aprovar
                                    </Button>
                                </>
                            )}
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </>
    );
}

AssetRequestShow.layout = {
    breadcrumbs: [
        { title: 'Solicitações', href: AssetRequestController.index.url() },
        { title: 'Detalhes', href: '#' },
    ],
};
