<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->label('Email address'),
                        TextEntry::make('email_verified_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('role'),
                        TextEntry::make('department_id')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('position')
                            ->placeholder('-'),
                        TextEntry::make('employee_id')
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->placeholder('-'),
                        IconEntry::make('is_active')
                            ->boolean(),
                    ])->columns(2)
                    ->columnSpan(['lg' => fn(?User $record) => $record === null ? 3 : 2]),
                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->state(fn(User $record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label('Last modified at')
                            ->state(fn(User $record): ?string => $record->updated_at?->diffForHumans()),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn(User $record): bool => $record->trashed()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?User $record) => $record === null),
            ])
            ->columns(3);

    }
}
