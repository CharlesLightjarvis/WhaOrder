<?php

namespace App\Repositories\Customers;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCustomerRepository implements CustomerRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->withCount('addresses')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): Customer
    {
        return Customer::query()
            ->with('addresses')
            ->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
