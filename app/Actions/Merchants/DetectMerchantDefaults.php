<?php

namespace App\Actions\Merchants;

use App\Support\Currencies;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DetectMerchantDefaults
{
    private const string DEFAULT_TIMEZONE = 'UTC';

    private const string DEFAULT_CURRENCY = 'USD';

    /**
     * Prefill a new merchant's currency and timezone from their IP address,
     * using a free geolocation lookup. Always falls back to sane defaults
     * (never throws) since this is just a convenience prefill — the
     * merchant can always correct it afterward in settings.
     *
     * @return array{currency: string, timezone: string}
     */
    public function handle(?string $ip): array
    {
        $resolvedIp = $ip;

        // On a local machine (dev, or the app server itself) the visitor's
        // IP is private/reserved and can't be geolocated directly — resolve
        // the machine's real public IP first so detection still works.
        if (! $resolvedIp || $this->isPrivateOrReserved($resolvedIp)) {
            $resolvedIp = $this->resolvePublicIp();
        }

        if (! $resolvedIp) {
            return $this->defaults();
        }

        // The free geolocation API rate-limits rapid successive requests
        // from the same IP (e.g. clicking "detect" repeatedly, or the
        // server's own shared IP in local dev). Remember a successful
        // lookup for a while so repeated calls stay consistent instead of
        // silently falling back to defaults when rate-limited. A failed
        // lookup is never cached, so the next attempt can retry cleanly.
        $cacheKey = "merchant-geolocation-defaults:{$resolvedIp}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $result = $this->lookup($resolvedIp);

        if ($result === null) {
            return $this->defaults();
        }

        Cache::put($cacheKey, $result, now()->addMinutes(30));

        return $result;
    }

    /**
     * @return array{currency: string, timezone: string}|null
     */
    private function lookup(string $ip): ?array
    {
        try {
            $response = Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");

            if ($response->failed() || $response->json('error')) {
                return null;
            }

            $currency = mb_strtoupper((string) $response->json('currency'));

            return [
                'currency' => Currencies::isValid($currency) ? $currency : self::DEFAULT_CURRENCY,
                'timezone' => $response->json('timezone') ?: self::DEFAULT_TIMEZONE,
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to detect merchant defaults from IP geolocation.', [
                'ip' => $ip,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function isPrivateOrReserved(string $ip): bool
    {
        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    private function resolvePublicIp(): ?string
    {
        try {
            $response = Http::timeout(3)->get('https://api.ipify.org', ['format' => 'json']);

            return $response->ok() ? $response->json('ip') : null;
        } catch (Throwable $exception) {
            Log::warning('Failed to resolve the server\'s public IP for geolocation.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{currency: string, timezone: string}
     */
    private function defaults(): array
    {
        return [
            'currency' => self::DEFAULT_CURRENCY,
            'timezone' => self::DEFAULT_TIMEZONE,
        ];
    }
}
