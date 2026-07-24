<?php

namespace App\Filament\Resources\ApprovalFlows\Schemas;

use App\Enums\RoleEnum;
use App\Models\ApprovalLevel;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class ApprovalFlowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Approval Flow')
                ->description('Menentukan tier persetujuan berdasarkan departemen & rentang nominal Purchase Request.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Flow')
                        ->required()
                        ->maxLength(255),

                    Select::make('department_id')
                        ->label('Departemen')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua departemen')
                        ->helperText('Kosongkan agar flow ini berlaku untuk semua departemen.'),

                    TextInput::make('min_amount')
                        ->label('Nominal Minimum')
                        ->required()
                        ->numeric()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->prefix('Rp')
                        ->default(0),

                    TextInput::make('max_amount')
                        ->label('Nominal Maksimum')
                        ->required()
                        ->numeric()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->prefix('Rp')
                        ->gte('min_amount')
                        ->helperText('Harus lebih besar atau sama dengan nominal minimum.'),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->helperText('Flow nonaktif tidak akan dipakai untuk menentukan approver.'),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Level Persetujuan')
                ->description('Urutan level menentukan siapa yang approve lebih dulu. Geser untuk mengubah urutan.')
                ->schema([
                    Repeater::make('levels')
                        ->relationship()
                        ->orderColumn('level_order')
                        ->reorderable()
                        ->collapsible()
                        ->addActionLabel('Tambah Level')
                        ->schema([
                            Select::make('role_type')
                                ->label('Tipe Approver')
                                ->options([
                                    ApprovalLevel::ROLE_TYPE_SECTION_HEAD => 'Section Head (departemen pemohon)',
                                    ApprovalLevel::ROLE_TYPE_DIVISION_HEAD => 'Division Head',
                                    ApprovalLevel::ROLE_TYPE_FINANCE_ADMIN => 'Finance Admin',
                                    ApprovalLevel::ROLE_TYPE_TREASURER => 'Treasurer',
                                    ApprovalLevel::ROLE_TYPE_DEPARTMENT_HEAD => 'Department Head (head_user_id)',
                                    ApprovalLevel::ROLE_TYPE_SPECIFIC_USER => 'User tertentu',
                                    ApprovalLevel::ROLE_TYPE_ROLE_BASED => 'Berdasarkan role lain',
                                ])
                                ->required()
                                ->live()
                                ->columnSpan(1),

                            Select::make('approver_id')
                                ->label('Pilih User')
                                ->relationship('approver', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->visible(fn (Get $get) => $get('role_type') === ApprovalLevel::ROLE_TYPE_SPECIFIC_USER)
                                ->columnSpan(1),

                            Select::make('role_name')
                                ->label('Role')
                                ->options(RoleEnum::labelMap())
                                ->required()
                                ->visible(fn (Get $get) => $get('role_type') === ApprovalLevel::ROLE_TYPE_ROLE_BASED)
                                ->columnSpan(1),

                            TextInput::make('description')
                                ->label('Keterangan')
                                ->maxLength(255)
                                ->columnSpan(1),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
