<?php

declare(strict_types=1);

namespace App\Filament\Resources\SiteSettings\Tables;

use App\Enums\SiteSettingType;
use App\Models\SiteSetting;
use App\Services\Settings\SiteSettingWriter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(SiteSetting::query())
            ->columns([
                TextColumn::make('key')
                    ->label('Khóa (Key)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('group')
                    ->label('Nhóm')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Định dạng')
                    ->badge(),

                IconColumn::make('is_public')
                    ->label('Công khai')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Nhóm cấu hình')
                    ->options(fn () => SiteSetting::distinct()->pluck('group', 'group')->toArray()),

                SelectFilter::make('type')
                    ->label('Định dạng')
                    ->options(SiteSettingType::class),

                TernaryFilter::make('is_public')
                    ->label('Công khai'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(fn (SiteSetting $record, SiteSettingWriter $writer) => $writer->delete($record)),
            ])
            ->bulkActions([]); // Bulk delete strictly disabled
    }
}
