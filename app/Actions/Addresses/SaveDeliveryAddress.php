<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use App\Repositories\Addresses\AddressRepository;

class SaveDeliveryAddress
{
    public function __construct(
        private readonly AddressRepository $repository,
    ) {}

    /**
     * Persist a delivery address as the customer's default, reusing an
     * existing matching one instead of creating a duplicate.
     */
    public function handle(string $customerId, ?string $addressText, ?string $city): Address
    {
        $existing = Address::query()
            ->where('customer_id', $customerId)
            ->where('line1', $addressText)
            ->where('city', $city)
            ->first();

        if ($existing) {
            if (! $existing->is_default) {
                Address::query()->where('customer_id', $customerId)->update(['is_default' => false]);
                $existing->update(['is_default' => true]);
            }

            return $existing;
        }

        Address::query()->where('customer_id', $customerId)->update(['is_default' => false]);

        return $this->repository->create([
            'customer_id' => $customerId,
            'line1' => $addressText,
            'city' => $city,
            'is_default' => true,
        ]);
    }
}
