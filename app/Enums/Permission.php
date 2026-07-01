<?php

namespace App\Enums;

enum Permission: string
{
    case BranchesView = 'branches.view';
    case BranchesManage = 'branches.manage';
    case UsersView = 'users.view';
    case UsersManage = 'users.manage';
    case RolesAssign = 'roles.assign';
    case RequestsView = 'requests.view';
    case RequestsCreate = 'requests.create';
    case RequestsApprove = 'requests.approve';
    case TransfersView = 'transfers.view';
    case TransfersCreate = 'transfers.create';
    case TransfersAuthorize = 'transfers.authorize';
    case DispatchView = 'dispatch.view';
    case DispatchExecute = 'dispatch.execute';
    case DispatchReceive = 'dispatch.receive';
    case AssetsView = 'assets.view';
    case AssetsManage = 'assets.manage';

    /**
     * All permission values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
