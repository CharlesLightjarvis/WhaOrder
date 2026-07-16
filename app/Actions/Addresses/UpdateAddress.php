<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use App\Repositories\Addresses\AddressRepository;
use Illuminate\Support\Facades\DB;

class UpdateAddress
{
    public function __construct(
        private readonly AddressRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Address $address, array $data): Address
    {
        return DB::transaction(fn () => $this->repository->update($address, $data));
    }
}
