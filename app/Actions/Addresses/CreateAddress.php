<?php

namespace App\Actions\Addresses;

use App\Models\Address;
use App\Repositories\Addresses\AddressRepository;
use Illuminate\Support\Facades\DB;

class CreateAddress
{
    public function __construct(
        private readonly AddressRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Address
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }
}
