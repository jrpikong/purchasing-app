<?php

namespace App\Filament\Resources\PurchaseRequests\Schemas;

use App\Enums\Priority;
use App\Filament\Resources\PurchaseRequests\Pages\CreatePurchaseRequest;
use App\Filament\Resources\PurchaseRequests\Pages\EditPurchaseRequest;
use App\Models\Department;
use App\Models\User;
use App\Models\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PurchaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        $isEdit = fn ($livewire) => $livewire instanceof EditPurchaseRequest;

        return $schema->components([

            // ═══════════════════════════════════════════════════════════
            // SECTION 1 — Informasi Pengajuan
            // ═══════════════════════════════════════════════════════════
            Section::make('Informasi Pengajuan')
                ->description('Lengkapi informasi dasar untuk purchase request Anda.')
                ->icon('heroicon-o-document-text')
                ->iconColor('primary')
                ->schema([

                    Grid::make(2)->schema([

                        Select::make('department_id')
                            ->label('Departemen')
                            ->relationship('department', 'name')
                            ->options(
                                Department::where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih departemen...')
                            ->helperText('Departemen yang mengajukan permintaan pembelian.')
                            ->prefixIcon('heroicon-o-building-office-2'),

                        DatePicker::make('request_date')
                            ->label('Tanggal Pengajuan')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->maxDate(now())
                            ->helperText('Tanggal pengajuan dibuat.')
                            ->prefixIcon('heroicon-o-calendar'),

                        DatePicker::make('required_date')
                            ->label('Tanggal Dibutuhkan')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->minDate(now()->addDay())
                            ->after('request_date')
                            ->helperText('Kapan barang/jasa dibutuhkan paling lambat.')
                            ->prefixIcon('heroicon-o-calendar-days'),

                        Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low'    => 'Low — Tidak mendesak',
                                'medium' => 'Medium — Normal',
                                'high'   => 'High — Segera',
                                'urgent' => 'Urgent — Sangat Mendesak',
                            ])
                            ->default('medium')
                            ->required()
                            ->native(false)
                            ->helperText('Tingkat urgensi permintaan pembelian ini.')
                            ->prefixIcon('heroicon-o-flag'),

                    ]),

                    Textarea::make('purpose')
                        ->label('Tujuan Pembelian')
                        ->required()
                        ->rows(4)
                        ->maxLength(2000)
                        ->placeholder('Jelaskan secara rinci mengapa pembelian ini diperlukan, apa yang akan dibeli, dan bagaimana manfaatnya untuk operasional...')
                        ->helperText('Minimal 30 karakter. Semakin detail, semakin cepat proses persetujuan.')
                        ->minLength(30)
                        ->columnSpanFull(),

                ]),

            // ═══════════════════════════════════════════════════════════
            // SECTION 2 — Estimasi Anggaran
            // ═══════════════════════════════════════════════════════════
            Section::make('Estimasi Anggaran')
                ->description('Masukkan perkiraan nilai pengadaan dan catatan tambahan.')
                ->icon('heroicon-o-banknotes')
                ->iconColor('success')
                ->schema([

                    Grid::make(2)->schema([

                        TextInput::make('total_amount')
                            ->label('Estimasi Total Anggaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->placeholder('0')
                            ->minValue(0)
                            ->helperText('Perkiraan total nilai pembelian dalam Rupiah. Menentukan level approval yang dibutuhkan.')
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->extraInputAttributes(['class' => 'text-right'])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set) {
                                // trigger live update for approval flow hint
                            }),

                        Placeholder::make('approval_tier_hint')
                            ->label('Tier Approval')
                            ->content(function (Get $get): string {
                                $amount = (float) ($get('total_amount') ?? 0);
                                if ($amount <= 0) {
                                    return '—  Masukkan estimasi anggaran untuk melihat tier approval.';
                                }
                                if ($amount <= 10_000_000) {
                                    return '✅  Standard (1 level) — Section Head';
                                }
                                if ($amount <= 50_000_000) {
                                    return '📋  Management (2 level) — Section Head → Division Head';
                                }
                                return '🏛️  Executive (4 level) — Section → Division → Finance → Treasurer';
                            })
                            ->helperText('Tier ditentukan otomatis berdasarkan jumlah anggaran.'),

                    ]),

                    Textarea::make('notes')
                        ->label('Catatan Tambahan')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Catatan atau keterangan tambahan yang perlu diketahui approver...')
                        ->helperText('Opsional. Informasi tambahan yang relevan.')
                        ->columnSpanFull(),

                ]),

            // ═══════════════════════════════════════════════════════════
            // SECTION 3 — Informasi Vendor
            // ═══════════════════════════════════════════════════════════
            Section::make('Informasi Vendor & Dokumen')
                ->description('Lampirkan informasi vendor yang diinginkan beserta dokumen quotation.')
                ->icon('heroicon-o-building-storefront')
                ->iconColor('warning')
                ->collapsible()
                ->schema([

                    Grid::make(2)->schema([

                        Select::make('preferred_vendor_id')
                            ->label('Vendor Terdaftar (Opsional)')
                            ->options(
                                Vendor::where('is_active', true)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn ($v) => [
                                        $v->id => "{$v->name} ({$v->vendor_code})"
                                    ])
                            )
                            ->searchable()
                            ->nullable()
                            ->placeholder('Pilih vendor terdaftar...')
                            ->helperText('Pilih jika vendor sudah terdaftar di sistem.')
                            ->prefixIcon('heroicon-o-check-circle')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $vendor = Vendor::find($state);
                                    if ($vendor) {
                                        $set('preferred_vendor_name', $vendor->name);
                                    }
                                }
                            }),

                        TextInput::make('preferred_vendor_name')
                            ->label('Nama Vendor (Manual)')
                            ->maxLength(255)
                            ->nullable()
                            ->placeholder('Contoh: PT. Sumber Makmur')
                            ->helperText('Isi jika vendor belum terdaftar di sistem.')
                            ->prefixIcon('heroicon-o-pencil'),

                    ]),

                    Textarea::make('preferred_vendor_reason')
                        ->label('Alasan Pemilihan Vendor')
                        ->rows(2)
                        ->maxLength(500)
                        ->nullable()
                        ->placeholder('Jelaskan mengapa vendor ini dipilih (harga kompetitif, kualitas, pengalaman, dll)...')
                        ->columnSpanFull(),

                    Grid::make(2)->schema([

                        TextInput::make('vendor_marketplace_link_1')
                            ->label('Link Quotation / Marketplace 1')
                            ->url()
                            ->nullable()
                            ->placeholder('https://...')
                            ->helperText('Link produk dari marketplace atau website vendor pertama.')
                            ->prefixIcon('heroicon-o-link'),

                        TextInput::make('vendor_marketplace_link_2')
                            ->label('Link Quotation / Marketplace 2')
                            ->url()
                            ->nullable()
                            ->placeholder('https://...')
                            ->helperText('Link pembanding dari vendor kedua (direkomendasikan).')
                            ->prefixIcon('heroicon-o-link'),

                    ]),

                    FileUpload::make('quotation_files')
                        ->label('Upload File Quotation')
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->maxFiles(5)
                        ->directory('quotations')
                        ->visibility('private')
                        ->downloadable()
                        ->reorderable()
                        ->appendFiles()
                        ->helperText('Format PDF, maksimal 10MB per file, maksimal 5 file. Upload minimal 1 quotation.')
                        ->columnSpanFull(),

                ]),

            // ═══════════════════════════════════════════════════════════
            // SECTION 4 — Assignment & Approval (Edit only, Admin only)
            // ═══════════════════════════════════════════════════════════
            Section::make('Assignment & Approval')
                ->description('Kelola penugasan PIC dan alur persetujuan PR ini.')
                ->icon('heroicon-o-user-circle')
                ->iconColor('info')
                ->collapsible()
                ->schema([

                    Grid::make(2)->schema([

                        Select::make('assigned_pic_id')
                            ->label('Assigned PIC')
                            ->options(
                                User::where('is_active', true)
                                    ->whereIn('role', ['admin', 'super_admin'])
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->nullable()
                            ->placeholder('Pilih PIC...')
                            ->helperText('Staf yang bertanggung jawab memproses pengadaan.')
                            ->prefixIcon('heroicon-o-user'),

                        Select::make('current_approver_id')
                            ->label('Current Approver')
                            ->options(
                                User::where('is_active', true)
                                    ->approvers()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->nullable()
                            ->placeholder('Pilih approver...')
                            ->helperText('Approver yang saat ini menangani PR.')
                            ->prefixIcon('heroicon-o-check-badge'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft'            => 'Draft',
                                'waiting_approval' => 'Waiting Approval',
                                'in_review'        => 'In Review',
                                'approved'         => 'Approved',
                                'rejected'         => 'Rejected',
                                'need_revision'    => 'Need Revision',
                                'completed'        => 'Completed',
                                'cancelled'        => 'Cancelled',
                            ])
                            ->required()
                            ->disabled()
                            ->prefixIcon('heroicon-o-tag'),

                        DateTimePicker::make('approval_deadline')
                            ->label('Batas Waktu Approval')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d M Y H:i')
                            ->minDate(now())
                            ->helperText('Deadline approver harus memberikan keputusan.')
                            ->prefixIcon('heroicon-o-clock'),

                    ]),

                ])
                ->visible($isEdit),

        ]);
    }
}
