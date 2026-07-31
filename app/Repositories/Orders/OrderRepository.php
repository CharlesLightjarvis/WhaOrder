<?php

namespace App\Repositories\Orders;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepository
{
    /** @return LengthAwarePaginator<int, Order> */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): Order;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order;
}
