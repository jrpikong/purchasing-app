<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The `role` column defaulted to 'admin' and the Filament user form never set it,
     * so every user created there looked like an admin to code reading the column.
     * Spatie roles are the source of truth: default the column to 'requester' and
     * copy each user's (most privileged) Spatie role into it.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('requester')->change();
        });

        $tables = config('permission.table_names');
        $morphKey = config('permission.column_names.model_morph_key', 'model_id');
        $morphClass = (new User)->getMorphClass();

        $usersWithRole = fn (?string $roleName) => DB::table($tables['model_has_roles'])
            ->join($tables['roles'], $tables['roles'] . '.id', '=', $tables['model_has_roles'] . '.role_id')
            ->where($tables['model_has_roles'] . '.model_type', $morphClass)
            ->when($roleName, fn ($q) => $q->where($tables['roles'] . '.name', $roleName))
            ->select($tables['model_has_roles'] . '.' . $morphKey);

        // Users with only non-app Spatie roles → requester
        DB::table('users')->whereIn('id', $usersWithRole(null))->update(['role' => User::ROLE_REQUESTER]);

        // Lowest → highest privilege, so the most privileged role is written last and wins
        foreach ([
            User::ROLE_REQUESTER, User::ROLE_APPROVER, User::ROLE_SECTION_HEAD, User::ROLE_DIVISION_HEAD,
            User::ROLE_FINANCE_ADMIN, User::ROLE_TREASURER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN,
        ] as $role) {
            DB::table('users')->whereIn('id', $usersWithRole($role))->update(['role' => $role]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->change();
        });
        // Synced role values are left as they are — they are more correct than before.
    }
};
