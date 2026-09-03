<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Services\Booking\BookingRequestCreator;
use App\Services\Booking\BookingServiceCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $locale = app()->getLocale();

        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/\A[0-9+\s().-]{7,30}\z/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'service_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($locale): void {
                    if (! app(BookingServiceCatalog::class)->isPubliclyEligible((int) $value, $locale)) {
                        $fail(__('booking.validation.service_id'));
                    }
                },
            ],
            'preferred_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    try {
                        $date = CarbonImmutable::parse((string) $value, BookingRequestCreator::BUSINESS_TIMEZONE)->startOfDay();
                    } catch (\Throwable) {
                        return;
                    }

                    $today = CarbonImmutable::now(BookingRequestCreator::BUSINESS_TIMEZONE)->startOfDay();

                    if ($date->lt($today)) {
                        $fail(__('booking.validation.preferred_date'));
                    }
                },
            ],
            'preferred_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedBookingData(): array
    {
        $validated = $this->validated();

        return [
            'customer_name' => trim((string) $validated['customer_name']),
            'phone' => trim((string) $validated['phone']),
            'email' => isset($validated['email']) ? trim((string) $validated['email']) : null,
            'service_id' => (int) $validated['service_id'],
            'preferred_date' => (string) $validated['preferred_date'],
            'preferred_time' => (string) $validated['preferred_time'],
            'customer_note' => isset($validated['notes']) ? trim((string) $validated['notes']) : null,
        ];
    }
}
