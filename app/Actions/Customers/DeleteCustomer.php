<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use App\Repositories\Customers\CustomerRepository;
use Illuminate\Support\Facades\DB;

class DeleteCustomer
{
    public function __construct(
        private readonly CustomerRepository $repository,
    ) {}

    public function handle(Customer $customer): void
    {
        DB::transaction(fn () => $this->repository->delete($customer));
    }
}
