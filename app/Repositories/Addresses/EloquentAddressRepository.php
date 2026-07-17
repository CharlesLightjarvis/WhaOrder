<?php

namespace App\Repositories\Addresses;

use App\Models\Address;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAddressRepository implements AddressRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Address::query()
            ->with('customer:id,name,whatsapp_number')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(string $id): Address
    {
        return Address::query()
            ->with('customer:id,name,whatsapp_number')
            ->findOrFail($id);
    }

    public function create(array $data): Address
    {
        return Address::query()->create($data);
    }

    public function update(Address $address, array $data): Address
    {
        $address->update($data);

        return $address;
    }

    public function delete(Address $address): void
    {
        $address->delete();
    }
}
