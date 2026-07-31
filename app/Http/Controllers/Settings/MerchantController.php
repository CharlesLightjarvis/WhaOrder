<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Merchants\DetectMerchantDefaults;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MerchantUpdateRequest;
use App\Support\Currencies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MerchantController extends Controller
{
    public function edit(Request $request): Response
    {
        $merchant = $request->user()->merchant;

        return Inertia::render('settings/merchant', [
            'merchant' => [
                'name' => $merchant->name,
                'whatsapp_admin_number' => $merchant->whatsapp_admin_number,
                'currency' => $merchant->currency,
                'timezone' => $merchant->timezone,
                'delivery_fee' => (float) $merchant->delivery_fee,
            ],
            'currencies' => Currencies::list(),
        ]);
    }

    public function update(MerchantUpdateRequest $request): RedirectResponse
    {
        $request->user()->merchant->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Business settings updated.')]);

        return to_route('merchant.edit');
    }

    /**
     * Detect the currency/timezone defaults for the requester's current IP,
     * so the settings form can offer a "detect automatically" refresh.
     */
    public function detectLocation(Request $request, DetectMerchantDefaults $detectMerchantDefaults): JsonResponse
    {
        $defaults = $detectMerchantDefaults->handle($request->ip());

        return response()->json([
            'currency' => $defaults['currency'],
            'timezone' => $defaults['timezone'],
        ]);
    }
}
