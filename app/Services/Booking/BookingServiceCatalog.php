<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Enums\ContentStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BookingServiceCatalog
{
    /**
     * @return Collection<int, array{id: int, name: string, price_summary: string|null}>
     */
    public function optionsForLocale(string $locale): Collection
    {
        return Service::query()
            ->where('status', ContentStatus::PUBLISHED)
            ->whereHas('translations', fn (Builder $query) => $query->where('locale', $locale))
            ->with([
                'translations' => fn ($query) => $query->where('locale', $locale),
                'prices' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc'),
            ])
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function (Service $service) use ($locale) {
                $translation = $service->translations->firstWhere('locale', $locale);
                $price = $service->prices->first();

                return [
                    'id' => $service->id,
                    'name' => (string) $translation?->name,
                    'price_summary' => $price ? number_format((int) $price->price_amount, 0, ',', '.').' ₫' : null,
                ];
            });
    }

    public function isPubliclyEligible(int $serviceId, string $locale): bool
    {
        if ($serviceId <= 0) {
            return false;
        }

        return Service::query()
            ->whereKey($serviceId)
            ->where('status', ContentStatus::PUBLISHED)
            ->whereHas('translations', fn (Builder $query) => $query->where('locale', $locale))
            ->exists();
    }
}
