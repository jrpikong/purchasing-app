<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                ->schema([
                    TextInput::make('name')
                        ->required(),

                    TextInput::make('phone')
                        ->tel(),
                    TextInput::make('email')
                        ->label('Email address')
                        ->email(),
                    TextInput::make('contact_person'),
                    TextInput::make('contact_phone')
                        ->tel(),
                    TextInput::make('tax_number'),
                    TextInput::make('bank_name'),
                    TextInput::make('bank_account'),
                    TextInput::make('bank_account_name'),
                    Toggle::make('is_active')
                        ->required(),
                    Textarea::make('address')
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->columnSpanFull(),
                ])
                ->columns()
                ->columnSpanFull()
            ]);
    }
}
