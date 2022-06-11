<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use KgBot\LaravelLocalization\Facades\ExportLocalizations;

class LocalizationController extends Controller
{
    public function lang($locale)
    {
        $locales = $this->collectLocales();

        if (
            ! $locales->get($locale) ||
            $this->doesNotHaveMessages($locales, $locale)
        ) {
            return $this->sendFallbackLocaleResponse($locales);
        }

        return $this->sendLocalizationResponse($locales, $locale);
    }

    protected function collectLocales(): Collection
    {
        return collect(ExportLocalizations::export()->toArray());
    }

    private function doesNotHaveMessages(Collection $locales, $locale): bool
    {
        return $locales->only($locale)
                ->pluck($this->includeOnly())
                ->first() === null;
    }

    protected function includeOnly(): array
    {
        return config('laravel-localization.include_only');
    }

    protected function sendFallbackLocaleResponse(Collection $locales)
    {
        $fallbackLocale = config('app.locale');

        return $this->sendLocalizationResponse($locales, $fallbackLocale);
    }

    protected function sendLocalizationResponse(Collection $locales, string $locale)
    {
        $messages = $locales->only($locale)
                  ->pluck($this->includeOnly())
                  ->first();

        return response()->json(['messages' => $messages]);
    }
}
