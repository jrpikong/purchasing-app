<?php

namespace App\Filament\Resources\ApprovalFlows;

use App\Filament\Resources\ApprovalFlows\Pages\CreateApprovalFlow;
use App\Filament\Resources\ApprovalFlows\Pages\EditApprovalFlow;
use App\Filament\Resources\ApprovalFlows\Pages\ListApprovalFlows;
use App\Filament\Resources\ApprovalFlows\Pages\ViewApprovalFlow;
use App\Filament\Resources\ApprovalFlows\Schemas\ApprovalFlowForm;
use App\Filament\Resources\ApprovalFlows\Schemas\ApprovalFlowInfolist;
use App\Filament\Resources\ApprovalFlows\Tables\ApprovalFlowsTable;
use App\Models\ApprovalFlow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApprovalFlowResource extends Resource
{
    protected static ?string $model = ApprovalFlow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Approval Flow';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ApprovalFlowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApprovalFlowInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApprovalFlowsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApprovalFlows::route('/'),
            'create' => CreateApprovalFlow::route('/create'),
            'view' => ViewApprovalFlow::route('/{record}'),
            'edit' => EditApprovalFlow::route('/{record}/edit'),
        ];
    }
}
