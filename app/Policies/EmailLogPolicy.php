<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Email logs contain recipients' addresses and delivery errors — admins only.
 * The log is an audit trail written by the system, so nobody creates, edits or deletes rows.
 *
 * Note: deliberately not a Shield "ViewAny:EmailLog" permission policy, so no role
 * setup is needed. If `shield:generate` is ever run for this resource, keep this file.
 */
class EmailLogPolicy
{
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isAdmin();
    }

    public function view(AuthUser $authUser, EmailLog $emailLog): bool
    {
        return $this->viewAny($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, EmailLog $emailLog): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, EmailLog $emailLog): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}
