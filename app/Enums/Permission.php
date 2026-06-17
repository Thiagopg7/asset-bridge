<?php

namespace App\Enums;

enum Permission: string
{
    case BranchesView = 'branches.view';
    case BranchesManage = 'branches.manage';
    case UsersView = 'users.view';
    case UsersManage = 'users.manage';
    case RolesAssign = 'roles.assign';
    case RequestsApprove = 'requests.approve';
    case TransfersAuthorize = 'transfers.authorize';
    case DispatchExecute = 'dispatch.execute';
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
