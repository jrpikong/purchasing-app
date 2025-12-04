<?php

namespace App\Filament\Exports;

use App\Models\PurchaseRequest;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PurchaseRequestExporter extends Exporter
{
    protected static ?string $model = PurchaseRequest::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('pr_number'),
            ExportColumn::make('requester.name'),
            ExportColumn::make('department.name'),
            ExportColumn::make('request_date'),
            ExportColumn::make('required_date'),
            ExportColumn::make('submitted_at'),
            ExportColumn::make('submitted_from'),
            ExportColumn::make('purpose'),
            ExportColumn::make('total_amount'),
            ExportColumn::make('preferred_vendor_name'),
            ExportColumn::make('preferredVendor.name'),
            ExportColumn::make('preferred_vendor_reason'),
            ExportColumn::make('vendor_marketplace_link_1'),
            ExportColumn::make('vendor_marketplace_link_2'),
            ExportColumn::make('quotation_files'),
            ExportColumn::make('status'),
            ExportColumn::make('currentApprover.name'),
            ExportColumn::make('assignedPic.name'),
            ExportColumn::make('sent_for_approval_at'),
            ExportColumn::make('approval_deadline'),
            ExportColumn::make('sectionHead.name'),
            ExportColumn::make('section_head_approved_at'),
            ExportColumn::make('divisionHead.name'),
            ExportColumn::make('division_head_approved_at'),
            ExportColumn::make('financeAdmin.name'),
            ExportColumn::make('finance_admin_approved_at'),
            ExportColumn::make('treasurer.name'),
            ExportColumn::make('treasurer_approved_at'),
            ExportColumn::make('finalApprover.name'),
            ExportColumn::make('approved_at'),
            ExportColumn::make('rejected_at'),
            ExportColumn::make('rejection_reason'),
            ExportColumn::make('notes'),
            ExportColumn::make('approval_token_expires_at'),
            ExportColumn::make('priority'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your purchase request export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
