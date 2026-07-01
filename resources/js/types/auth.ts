export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};

export type Branch = {
    id: number;
    name: string;
    code: string;
    city: string | null;
    state: string | null;
    active: boolean;
};

export type AssetUnit = 'un' | 'cx' | 'kg' | 'lt' | 'm';

export const ASSET_UNIT_LABELS: Record<AssetUnit, string> = {
    un: 'Unidade',
    cx: 'Caixa',
    kg: 'Quilograma',
    lt: 'Litro',
    m: 'Metro',
};

export type Asset = {
    id: number;
    name: string;
    description: string | null;
    unit: AssetUnit;
    active: boolean;
};

export type StockEntry = {
    asset_id: number;
    asset_name: string;
    unit: AssetUnit;
    quantity: number;
};

export type Can = {
    viewBranches: boolean;
    manageBranches: boolean;
    viewUsers: boolean;
    manageUsers: boolean;
    viewAssets: boolean;
    manageAssets: boolean;
    viewRequests: boolean;
    createRequests: boolean;
    approveRequests: boolean;
    viewTransfers: boolean;
    createTransfers: boolean;
    authorizeTransfers: boolean;
    viewShipments: boolean;
    executeDispatch: boolean;
    receiveShipments: boolean;
};

export type AssetRequestType = 'need' | 'surplus';

export const ASSET_REQUEST_TYPE_LABELS: Record<AssetRequestType, string> = {
    need: 'Necessidade',
    surplus: 'Excesso',
};

export type AssetRequestStatus = 'pending' | 'approved' | 'rejected';

export const ASSET_REQUEST_STATUS_LABELS: Record<AssetRequestStatus, string> = {
    pending: 'Pendente',
    approved: 'Aprovada',
    rejected: 'Rejeitada',
};

export type AssetRequestListItem = {
    id: number;
    type: AssetRequestType;
    type_label: string;
    status: AssetRequestStatus;
    status_label: string;
    quantity: number;
    notes: string | null;
    asset_name: string;
    unit: AssetUnit;
    branch_name: string;
    user_name: string;
    created_at: string;
    can_review: boolean;
    can_delete: boolean;
};

export type AssetOption = {
    id: number;
    name: string;
    unit: AssetUnit;
};

export type AssetRequestTypeOption = {
    value: AssetRequestType;
    label: string;
};

export type MarketplaceOffer = {
    id: number;
    asset_name: string;
    unit: AssetUnit;
    branch_name: string;
    quantity: number;
    available_quantity: number;
    notes: string | null;
    created_at: string;
};

export type TransferStatus = 'pending' | 'authorized' | 'rejected';

export const TRANSFER_STATUS_LABELS: Record<TransferStatus, string> = {
    pending: 'Pendente',
    authorized: 'Autorizada',
    rejected: 'Rejeitada',
};

export type TransferListItem = {
    id: number;
    quantity: number;
    status: TransferStatus;
    status_label: string;
    notes: string | null;
    asset_name: string;
    unit: AssetUnit;
    offer_branch_name: string;
    branch_name: string;
    user_name: string;
    created_at: string;
    can_review: boolean;
    can_delete: boolean;
};

export type ShipmentStatus = 'ready' | 'in_transit' | 'received';

export const SHIPMENT_STATUS_LABELS: Record<ShipmentStatus, string> = {
    ready: 'Pronto para envio',
    in_transit: 'Em trânsito',
    received: 'Recebido',
};

export type ShipmentListItem = {
    id: number;
    asset_name: string;
    unit: AssetUnit;
    quantity: number;
    origin_branch_name: string;
    destination_branch_name: string;
    status: ShipmentStatus;
    status_label: string;
    created_at: string;
    can_dispatch: boolean;
    can_receive: boolean;
};

export type UserListItem = {
    id: number;
    name: string;
    email: string;
    branch_id: number | null;
    branch: { id: number; name: string } | null;
    role: string | null;
};

export type UserFormData = {
    id: number;
    name: string;
    email: string;
    branch_id: number | null;
    role: string | null;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};
