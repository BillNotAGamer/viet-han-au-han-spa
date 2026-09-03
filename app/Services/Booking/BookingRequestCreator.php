<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingRequestCreator
{
    public const BUSINESS_TIMEZONE = 'Asia/Ho_Chi_Minh';

    public function __construct(
        protected BookingServiceCatalog $serviceCatalog
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, string $locale, ?Request $request = null): Booking
    {
        if (! $this->serviceCatalog->isPubliclyEligible((int) $data['service_id'], $locale)) {
            throw new \InvalidArgumentException('Selected service is not publicly bookable for this locale.');
        }

        return DB::transaction(function () use ($data, $locale, $request): Booking {
            $service = Service::query()
                ->with([
                    'translations' => fn ($query) => $query->where('locale', $locale),
                    'prices' => fn ($query) => $query
                        ->where('is_active', true)
                        ->with(['translations' => fn ($translationQuery) => $translationQuery->where('locale', $locale)])
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('id', 'asc'),
                ])
                ->findOrFail((int) $data['service_id']);

            $price = $service->prices->first();

            return Booking::create([
                'reference' => $this->makeReference(),
                'service_id' => $service->id,
                'service_price_id' => $price?->id,
                'service_name_snapshot' => $service->translationFor($locale)?->name,
                'service_price_label_snapshot' => $price ? $this->priceLabel($price, $locale) : null,
                'duration_minutes_snapshot' => $price?->duration_minutes,
                'price_amount_snapshot' => $price?->price_amount,
                'customer_name' => trim((string) $data['customer_name']),
                'phone' => trim((string) $data['phone']),
                'phone_normalized' => $this->normalizePhone((string) $data['phone']),
                'email' => $data['email'] !== null && $data['email'] !== '' ? trim((string) $data['email']) : null,
                'preferred_date' => $data['preferred_date'],
                'preferred_time' => $data['preferred_time'],
                'guest_count' => 1,
                'customer_note' => $data['customer_note'] !== null && $data['customer_note'] !== '' ? trim((string) $data['customer_note']) : null,
                'admin_note' => null,
                'status' => BookingStatus::NEW,
                'locale' => $locale,
                'landing_page' => $request?->fullUrl(),
                'referrer' => $request?->headers->get('referer'),
            ]);
        });
    }

    protected function makeReference(): string
    {
        do {
            $reference = 'BK-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Booking::query()->where('reference', $reference)->exists());

        return $reference;
    }

    protected function normalizePhone(string $phone): string
    {
        $normalized = preg_replace('/[^0-9+]/', '', trim($phone)) ?: '';

        if (str_starts_with($normalized, '00')) {
            $normalized = '+'.substr($normalized, 2);
        }

        if (str_starts_with($normalized, '0')) {
            $normalized = '+84'.substr($normalized, 1);
        }

        return $normalized;
    }

    protected function priceLabel(ServicePrice $price, string $locale): ?string
    {
        $label = $price->translations->firstWhere('locale', $locale)?->label;

        return $label !== null && trim($label) !== '' ? trim($label) : null;
    }
}
