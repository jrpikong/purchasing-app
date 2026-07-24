<?php

namespace App\Filament\Resources\ApprovalFlows\Schemas;

use App\Models\ApprovalFlow;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApprovalFlowInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Approval Flow')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('department.name')->label('Departemen')->default('Semua Departemen'),
                    TextEntry::make('min_amount')->label('Min')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                    TextEntry::make('max_amount')->label('Max')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                    TextEntry::make('is_active')->label('Aktif')->badge()->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
                    TextEntry::make('description')->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Urutan Level Persetujuan')
                ->schema([
                    TextEntry::make('levels')
                        ->label('')
                        ->state(fn (ApprovalFlow $record) => $record->levels->isEmpty()
                            ? 'Belum ada level yang dikonfigurasi.'
                            : $record->levels
                                ->sortBy('level_order')
                                ->map(fn ($level) => "{$level->level_order}. {$level->approver_display_name}")
                                ->implode("\n"))
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
