<?php

namespace App\Actions\Customers;

use App\Models\Address;
use App\Models\Customer;
use App\Repositories\Customers\CustomerRepository;
use Illuminate\Support\Facades\DB;

class UpdateCustomer
{
    public function __construct(
        private readonly CustomerRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $addressesData = $data['addresses'] ?? [];
            unset($data['addresses']);

            $customer = $this->repository->update($customer, $data);

            $keptAddressIds = [];

            foreach ($addressesData as $addressData) {
                $address = isset($addressData['id'])
                    ? $customer->addresses()->findOrFail($addressData['id'])
                    : new Address;

                unset($addressData['id']);
                $address->fill($addressData);

                $customer->addresses()->save($address);
                $keptAddressIds[] = $address->id;
            }

            $customer->addresses()->whereNotIn('id', $keptAddressIds)->delete();

            return $customer->fresh('addresses');
        });
    }
}
