<?php

namespace App\Repositories\Orders;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): Order;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order;
}
