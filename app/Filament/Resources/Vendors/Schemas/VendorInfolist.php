<?php

namespace App\Filament\Resources\Vendors\Schemas;

use App\Models\Vendor;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('vendor_code'),
                        TextEntry::make('name'),
                        TextEntry::make('phone')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->placeholder('-'),
                        TextEntry::make('contact_person')
                            ->placeholder('-'),
                        TextEntry::make('contact_phone')
                            ->placeholder('-'),
                        TextEntry::make('tax_number')
                            ->placeholder('-'),
                        TextEntry::make('bank_name')
                            ->placeholder('-'),
                        TextEntry::make('bank_account')
                            ->placeholder('-'),
                        TextEntry::make('bank_account_name')
                            ->placeholder('-'),
                        IconEntry::make('is_active')
                            ->boolean(),
                        TextEntry::make('address')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn(Vendor $record): bool => $record->trashed()),
                    ])
                    ->columns()
                    ->columnSpanFull()
            ]);
    }
}
