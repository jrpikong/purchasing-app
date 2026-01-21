<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\PositionEnum;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('employee_id')
                            ->placeholder('IT001')
                            ->label('Employee ID'),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->required()
                            ->maxLength(255)
                            ->email()
                            ->unique(User::class, 'email'),
                        DatePicker::make('membership_expired_at')
                            ->label('Membership Expired')
                            ->seconds(false)
                            ->nullable()
                            ->helperText('Jika terisi, user tidak bisa login setelah tanggal ini'),
                        TextInput::make('password')
                            ->password()
                            ->required(fn ($context) => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state)),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Select::make('department_id')
                            ->relationship('department', 'name')
                            ->required(),
                        Select::make('position')
                            ->options(PositionEnum::class),
                        TextInput::make('phone')
                            ->tel(),
                        Toggle::make('is_active')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn(?User $record) => $record === null ? 3 : 2]),
                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->state(fn(User $record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label('Last modified at')
                            ->state(fn(User $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?User $record) => $record === null),
            ])
            ->columns(3);
    }
}
