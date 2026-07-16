<?php

namespace App\Repositories\Addresses;

use App\Models\Address;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AddressRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): Address;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Address;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Address $address, array $data): Address;

    public function delete(Address $address): void;
}
