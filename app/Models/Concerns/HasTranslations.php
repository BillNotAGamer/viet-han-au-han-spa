<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\Localization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasTranslations
{
    /**
     * Retrieve the exact translation for a requested locale.
     * Reuses eager-loaded 'translations' collection if available to avoid N+1 query.
     */
    public function translationFor(string $locale): ?Model
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Retrieve the translation for a requested locale or fallback to default locale if not found.
     */
    public function translationOrFallback(string $locale, ?string $fallback = null): ?Model
    {
        $fallbackLocale = $fallback ?? Localization::fallbackLocale();

        $exact = $this->translationFor($locale);
        if ($exact !== null) {
            return $exact;
        }

        if ($locale !== $fallbackLocale) {
            return $this->translationFor($fallbackLocale);
        }

        return null;
    }

    /**
     * Scope a query to filter records by translated slug in a given locale.
     */
    public function scopeWhereSlug(Builder $query, string $locale, string $slug): Builder
    {
        return $query->whereHas('translations', function (Builder $q) use ($locale, $slug) {
            $q->where('locale', $locale)->where('slug', $slug);
        });
    }
}
