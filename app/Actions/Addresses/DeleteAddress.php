<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use App\Repositories\Addresses\AddressRepository;
use Illuminate\Support\Facades\DB;

class DeleteAddress
{
    public function __construct(
        private readonly AddressRepository $repository,
    ) {}

    public function handle(Address $address): void
    {
        DB::transaction(fn () => $this->repository->delete($address));
    }
}
