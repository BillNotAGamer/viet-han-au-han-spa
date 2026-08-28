<?php

declare(strict_types=1);

namespace App\Services\ServiceCatalog;

use App\Enums\ContentStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\ServicePriceTranslation;
use App\Models\ServiceTranslation;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? ContentStatus::DRAFT->value));

            $service = Service::create([
                'service_category_id' => (int) $data['service_category_id'],
                'hero_media_id' => ! empty($data['hero_media_id']) ? (int) $data['hero_media_id'] : null,
                'status' => $status,
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            // Save Vietnamese Translation (Required)
            $viName = trim((string) ($data['vi']['name'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viName);
            }

            ServiceTranslation::create([
                'service_id' => $service->id,
                'locale' => 'vi',
                'name' => $viName,
                'slug' => $viSlug,
                'excerpt' => $data['vi']['excerpt'] ?? null,
                'content' => $data['vi']['content'] ?? null,
                'benefits' => $this->sanitizeJsonRepeater($data['vi']['benefits'] ?? null),
                'process_steps' => $this->sanitizeJsonRepeater($data['vi']['process_steps'] ?? null),
                'faqs' => $this->sanitizeJsonRepeater($data['vi']['faqs'] ?? null),
                'seo_title' => $data['vi']['seo_title'] ?? null,
                'seo_description' => $data['vi']['seo_description'] ?? null,
            ]);

            // Save English Translation (Optional)
            $enName = trim((string) ($data['en']['name'] ?? ''));
            if ($enName !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enName);
                }

                ServiceTranslation::create([
                    'service_id' => $service->id,
                    'locale' => 'en',
                    'name' => $enName,
                    'slug' => $enSlug,
                    'excerpt' => $data['en']['excerpt'] ?? null,
                    'content' => $data['en']['content'] ?? null,
                    'benefits' => $this->sanitizeJsonRepeater($data['en']['benefits'] ?? null),
                    'process_steps' => $this->sanitizeJsonRepeater($data['en']['process_steps'] ?? null),
                    'faqs' => $this->sanitizeJsonRepeater($data['en']['faqs'] ?? null),
                    'seo_title' => $data['en']['seo_title'] ?? null,
                    'seo_description' => $data['en']['seo_description'] ?? null,
                ]);
            }

            // Synchronize Price Tiers
            $this->syncPriceTiers($service, $data['prices'] ?? []);

            // Synchronize Gallery Media
            $this->syncGalleryMedia($service, $data['gallery_media_ids'] ?? []);

            return $service->fresh(['translations', 'prices.translations', 'media', 'heroMedia']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data) {
            $status = $data['status'] instanceof ContentStatus
                ? $data['status']
                : ContentStatus::from((string) ($data['status'] ?? $service->status->value));

            $service->update([
                'service_category_id' => (int) ($data['service_category_id'] ?? $service->service_category_id),
                'hero_media_id' => ! empty($data['hero_media_id']) ? (int) $data['hero_media_id'] : null,
                'status' => $status,
                'is_featured' => (bool) ($data['is_featured'] ?? $service->is_featured),
                'sort_order' => (int) ($data['sort_order'] ?? $service->sort_order),
            ]);

            // Update Vietnamese Translation (In-place ID preservation)
            $viName = trim((string) ($data['vi']['name'] ?? ''));
            $viSlug = trim((string) ($data['vi']['slug'] ?? ''));
            if ($viSlug === '') {
                $viSlug = Str::slug($viName);
            }

            ServiceTranslation::updateOrCreate(
                [
                    'service_id' => $service->id,
                    'locale' => 'vi',
                ],
                [
                    'name' => $viName,
                    'slug' => $viSlug,
                    'excerpt' => $data['vi']['excerpt'] ?? null,
                    'content' => $data['vi']['content'] ?? null,
                    'benefits' => $this->sanitizeJsonRepeater($data['vi']['benefits'] ?? null),
                    'process_steps' => $this->sanitizeJsonRepeater($data['vi']['process_steps'] ?? null),
                    'faqs' => $this->sanitizeJsonRepeater($data['vi']['faqs'] ?? null),
                    'seo_title' => $data['vi']['seo_title'] ?? null,
                    'seo_description' => $data['vi']['seo_description'] ?? null,
                ]
            );

            // Update English Translation (In-place ID preservation)
            $enName = trim((string) ($data['en']['name'] ?? ''));
            if ($enName !== '') {
                $enSlug = trim((string) ($data['en']['slug'] ?? ''));
                if ($enSlug === '') {
                    $enSlug = Str::slug($enName);
                }

                ServiceTranslation::updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'locale' => 'en',
                    ],
                    [
                        'name' => $enName,
                        'slug' => $enSlug,
                        'excerpt' => $data['en']['excerpt'] ?? null,
                        'content' => $data['en']['content'] ?? null,
                        'benefits' => $this->sanitizeJsonRepeater($data['en']['benefits'] ?? null),
                        'process_steps' => $this->sanitizeJsonRepeater($data['en']['process_steps'] ?? null),
                        'faqs' => $this->sanitizeJsonRepeater($data['en']['faqs'] ?? null),
                        'seo_title' => $data['en']['seo_title'] ?? null,
                        'seo_description' => $data['en']['seo_description'] ?? null,
                    ]
                );
            }

            // Synchronize Price Tiers (ID-aware)
            $this->syncPriceTiers($service, $data['prices'] ?? []);

            // Synchronize Gallery Media
            $this->syncGalleryMedia($service, $data['gallery_media_ids'] ?? []);

            return $service->fresh(['translations', 'prices.translations', 'media', 'heroMedia']);
        });
    }

    /**
     * Consistent JSON empty-state policy: returns null if empty.
     *
     * @param  array<int, array<string, mixed>>|null  $items
     * @return array<int, array<string, mixed>>|null
     */
    protected function sanitizeJsonRepeater(?array $items): ?array
    {
        if (empty($items)) {
            return null;
        }

        $filtered = array_filter($items, function ($item) {
            if (! is_array($item)) {
                return false;
            }
            foreach ($item as $val) {
                if (filled($val)) {
                    return true;
                }
            }

            return false;
        });

        return ! empty($filtered) ? array_values($filtered) : null;
    }

    /**
     * ID-aware price tier synchronization.
     *
     * @param  array<int, array<string, mixed>>  $pricesData
     */
    protected function syncPriceTiers(Service $service, array $pricesData): void
    {
        $existingPrices = $service->prices()->get()->keyBy('id');
        $processedIds = [];

        foreach ($pricesData as $tier) {
            $priceId = ! empty($tier['id']) ? (int) $tier['id'] : null;

            if ($priceId !== null && $existingPrices->has($priceId)) {
                // Update existing tier with stable ID
                $price = $existingPrices->get($priceId);
                $price->update([
                    'duration_minutes' => (int) $tier['duration_minutes'],
                    'price_amount' => (int) $tier['price_amount'],
                    'sort_order' => (int) ($tier['sort_order'] ?? 0),
                    'is_active' => (bool) ($tier['is_active'] ?? true),
                ]);
                $processedIds[] = $priceId;
            } else {
                // Create new tier
                $price = ServicePrice::create([
                    'service_id' => $service->id,
                    'duration_minutes' => (int) $tier['duration_minutes'],
                    'price_amount' => (int) $tier['price_amount'],
                    'sort_order' => (int) ($tier['sort_order'] ?? 0),
                    'is_active' => (bool) ($tier['is_active'] ?? true),
                ]);
                $processedIds[] = $price->id;
            }

            // Sync Vietnamese label
            if (isset($tier['label_vi']) && trim((string) $tier['label_vi']) !== '') {
                ServicePriceTranslation::updateOrCreate(
                    ['service_price_id' => $price->id, 'locale' => 'vi'],
                    ['label' => trim((string) $tier['label_vi'])]
                );
            }

            // Sync English label
            if (isset($tier['label_en']) && trim((string) $tier['label_en']) !== '') {
                ServicePriceTranslation::updateOrCreate(
                    ['service_price_id' => $price->id, 'locale' => 'en'],
                    ['label' => trim((string) $tier['label_en'])]
                );
            }
        }

        // Handle omitted tiers: Check booking references
        foreach ($existingPrices as $oldPriceId => $oldPrice) {
            if (! in_array($oldPriceId, $processedIds, true)) {
                $hasBookings = Booking::where('service_price_id', $oldPriceId)->exists();
                if ($hasBookings) {
                    // Safe deactivation to preserve historical booking integrity
                    $oldPrice->update(['is_active' => false]);
                } else {
                    $oldPrice->translations()->delete();
                    $oldPrice->delete();
                }
            }
        }
    }

    /**
     * @param  array<int, int|string>  $mediaIds
     */
    protected function syncGalleryMedia(Service $service, array $mediaIds): void
    {
        $syncData = [];
        $order = 0;
        foreach (array_unique($mediaIds) as $mediaId) {
            if (! empty($mediaId)) {
                $syncData[(int) $mediaId] = [
                    'sort_order' => $order++,
                    'created_at' => now(),
                ];
            }
        }

        $service->media()->sync($syncData);
    }

    public function archive(Service $service): void
    {
        $service->update(['status' => ContentStatus::ARCHIVED]);
    }

    public function restore(Service $service): void
    {
        $service->update(['status' => ContentStatus::DRAFT]);
    }

    public function delete(Service $service): void
    {
        if ($service->status !== ContentStatus::DRAFT) {
            throw new DomainException('Only draft services can be deleted.');
        }

        if ($service->bookings()->exists()) {
            throw new DomainException('Cannot delete service because it has associated bookings.');
        }

        DB::transaction(function () use ($service) {
            $service->media()->detach();

            foreach ($service->prices as $price) {
                $price->translations()->delete();
                $price->delete();
            }

            $service->translations()->delete();
            $service->delete();
        });
    }
}
