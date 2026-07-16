<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use App\Repositories\Customers\CustomerRepository;
use Illuminate\Support\Facades\DB;

class CreateCustomer
{
    public function __construct(
        private readonly CustomerRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $addresses = $data['addresses'] ?? [];
            unset($data['addresses']);

            $customer = $this->repository->create($data);

            foreach ($addresses as $addressData) {
                $customer->addresses()->create($addressData);
            }

            return $customer->fresh('addresses');
        });
    }
}
