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

        // Free geolocation APIs can rate-limit or hiccup individually
        // (e.g. clicking "detect" repeatedly, or a shared IP in local dev).
        // Remember a successful lookup for a while so repeated calls stay
        // consistent. A failed lookup is never cached, so the next attempt
        // can retry cleanly.
        $cacheKey = "merchant-geolocation-defaults:{$resolvedIp}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $result = $this->lookupFreeIpApi($resolvedIp)
            ?? $this->lookupIpapiCo($resolvedIp)
            ?? $this->lookupIpApiCom($resolvedIp);

        if ($result === null) {
            return $this->defaults();
        }

        Cache::put($cacheKey, $result, now()->addMinutes(30));

        return $result;
    }

    /**
     * Primary provider: gives currency, timezone and country in one call,
     * with no rate limiting observed even under rapid repeated calls.
     *
     * @return array{currency: string, timezone: string}|null
     */
    private function lookupFreeIpApi(string $ip): ?array
    {
        return $this->tryLookup(function () use ($ip) {
            $response = Http::withUserAgent('WhaOrder/1.0')
                ->timeout(3)
                ->get("https://freeipapi.com/api/json/{$ip}");

            if ($response->failed()) {
                return null;
            }

            $currency = mb_strtoupper((string) ($response->json('currencies.0') ?? ''));
            $timezone = $response->json('timeZones.0');

            return [
                'currency' => Currencies::isValid($currency) ? $currency : self::DEFAULT_CURRENCY,
                'timezone' => $timezone ?: self::DEFAULT_TIMEZONE,
            ];
        });
    }

    /**
     * Secondary provider, tried if the primary one is down or changes its
     * terms — has a stricter daily quota, but is otherwise equivalent.
     *
     * @return array{currency: string, timezone: string}|null
     */
    private function lookupIpapiCo(string $ip): ?array
    {
        return $this->tryLookup(function () use ($ip) {
            $response = Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");

            if ($response->failed() || $response->json('error')) {
                return null;
            }

            $currency = mb_strtoupper((string) $response->json('currency'));

            return [
                'currency' => Currencies::isValid($currency) ? $currency : self::DEFAULT_CURRENCY,
                'timezone' => $response->json('timezone') ?: self::DEFAULT_TIMEZONE,
            ];
        });
    }

    /**
     * Last-resort provider — doesn't return a currency directly, so it's
     * derived from the country code via our own mapping.
     *
     * @return array{currency: string, timezone: string}|null
     */
    private function lookupIpApiCom(string $ip): ?array
    {
        return $this->tryLookup(function () use ($ip) {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");

            if ($response->failed() || $response->json('status') !== 'success') {
                return null;
            }

            $currency = Currencies::forCountry((string) $response->json('countryCode'));

            return [
                'currency' => $currency ?? self::DEFAULT_CURRENCY,
                'timezone' => $response->json('timezone') ?: self::DEFAULT_TIMEZONE,
            ];
        });
    }

    /**
     * @param  callable(): (array{currency: string, timezone: string}|null)  $callback
     * @return array{currency: string, timezone: string}|null
     */
    private function tryLookup(callable $callback): ?array
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            Log::warning('A geolocation provider failed while detecting merchant defaults.', [
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
