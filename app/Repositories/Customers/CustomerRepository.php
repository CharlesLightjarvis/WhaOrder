<?php

namespace App\Repositories\Customers;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): Customer;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): void;
}
