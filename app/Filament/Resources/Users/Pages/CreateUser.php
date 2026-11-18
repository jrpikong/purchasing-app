<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * The form sets Spatie roles only; mirror them into the legacy `role` column
     * (whose DB default is 'admin') so nothing reading the column mistakes this user for an admin.
     */
    protected function afterCreate(): void
    {
        $this->getRecord()->syncRoleColumnFromSpatie();
    }
}
