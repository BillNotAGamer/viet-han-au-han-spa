<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceCategories\Pages;

use App\Filament\Resources\ServiceCategories\ServiceCategoryResource;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalog\ServiceCategoryWriter;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditServiceCategory extends EditRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var ServiceCategory $record */
        $record = $this->getRecord();
        $record->loadMissing('translations');

        $vi = $record->translationFor('vi');
        $en = $record->translationFor('en');

        $data['vi'] = [
            'name' => $vi?->name,
            'slug' => $vi?->slug,
            'description' => $vi?->description,
            'seo_title' => $vi?->seo_title,
            'seo_description' => $vi?->seo_description,
        ];

        $data['en'] = [
            'name' => $en?->name,
            'slug' => $en?->slug,
            'description' => $en?->description,
            'seo_title' => $en?->seo_title,
            'seo_description' => $en?->seo_description,
        ];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var ServiceCategory $record */
        return app(ServiceCategoryWriter::class)->update($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action, ServiceCategory $record) {
                    if ($record->services()->count() > 0) {
                        Notification::make()
                            ->danger()
                            ->title('Không thể xóa')
                            ->body('Danh mục đang chứa dịch vụ, không thể xóa.')
                            ->send();

                        $action->halt();
                    }
                })
                ->using(function (ServiceCategory $record) {
                    app(ServiceCategoryWriter::class)->delete($record);
                }),
        ];
    }
}
