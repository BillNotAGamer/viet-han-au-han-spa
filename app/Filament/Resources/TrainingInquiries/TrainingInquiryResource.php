<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingInquiries;

use App\Filament\Resources\TrainingInquiries\Pages\ListTrainingInquiries;
use App\Filament\Resources\TrainingInquiries\Pages\ViewTrainingInquiry;
use App\Filament\Resources\TrainingInquiries\Schemas\TrainingInquiryInfolist;
use App\Filament\Resources\TrainingInquiries\Tables\TrainingInquiriesTable;
use App\Models\TrainingInquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TrainingInquiryResource extends Resource
{
    protected static ?string $model = TrainingInquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Đào tạo';

    protected static ?string $navigationLabel = 'Yêu cầu tư vấn';

    protected static ?string $modelLabel = 'Yêu cầu tư vấn';

    protected static ?string $pluralModelLabel = 'Yêu cầu tư vấn';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        // For lead safety, edit forms are not used. Views use Infolists.
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrainingInquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingInquiriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingInquiries::route('/'),
            'view' => ViewTrainingInquiry::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
