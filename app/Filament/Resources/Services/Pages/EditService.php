<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use App\Services\ServiceCatalog\ServiceWriter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Service $record */
        $record = $this->getRecord();
        $record->loadMissing(['translations', 'prices.translations', 'media']);

        $vi = $record->translationFor('vi');
        $en = $record->translationFor('en');

        $data['vi'] = [
            'name' => $vi?->name,
            'slug' => $vi?->slug,
            'excerpt' => $vi?->excerpt,
            'content' => $vi?->content,
            'benefits' => $vi?->benefits ?? [],
            'process_steps' => $vi?->process_steps ?? [],
            'faqs' => $vi?->faqs ?? [],
            'seo_title' => $vi?->seo_title,
            'seo_description' => $vi?->seo_description,
        ];

        $data['en'] = [
            'name' => $en?->name,
            'slug' => $en?->slug,
            'excerpt' => $en?->excerpt,
            'content' => $en?->content,
            'benefits' => $en?->benefits ?? [],
            'process_steps' => $en?->process_steps ?? [],
            'faqs' => $en?->faqs ?? [],
            'seo_title' => $en?->seo_title,
            'seo_description' => $en?->seo_description,
        ];

        $data['gallery_media_ids'] = $record->media->pluck('id')->toArray();

        $pricesArray = [];
        foreach ($record->prices as $price) {
            $priceVi = $price->translationFor('vi');
            $priceEn = $price->translationFor('en');

            $pricesArray[] = [
                'id' => $price->id,
                'duration_minutes' => $price->duration_minutes,
                'price_amount' => $price->price_amount,
                'sort_order' => $price->sort_order,
                'is_active' => $price->is_active,
                'label_vi' => $priceVi?->label,
                'label_en' => $priceEn?->label,
            ];
        }

        $data['prices'] = $pricesArray;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Service $record */
        return app(ServiceWriter::class)->update($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->label('Lưu trữ')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (Service $record) => $record->status !== ContentStatus::ARCHIVED)
                ->action(function (Service $record) {
                    app(ServiceWriter::class)->archive($record);
                    Notification::make()->success()->title('Dịch vụ đã được chuyển vào lưu trữ.')->send();
                }),

            Action::make('restore')
                ->label('Khôi phục nháp')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->visible(fn (Service $record) => $record->status === ContentStatus::ARCHIVED)
                ->action(function (Service $record) {
                    app(ServiceWriter::class)->restore($record);
                    Notification::make()->success()->title('Dịch vụ đã được khôi phục về bản nháp.')->send();
                }),

            DeleteAction::make()
                ->visible(fn (Service $record) => $record->status === ContentStatus::DRAFT && $record->bookings()->count() === 0)
                ->before(function (DeleteAction $action, Service $record) {
                    if ($record->bookings()->count() > 0) {
                        Notification::make()
                            ->danger()
                            ->title('Không thể xóa')
                            ->body('Dịch vụ đã có dữ liệu đặt lịch, không thể xóa.')
                            ->send();

                        $action->halt();
                    }
                })
                ->using(function (Service $record) {
                    app(ServiceWriter::class)->delete($record);
                }),
        ];
    }
}
