<?php

namespace App\Repositories\Orders;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with('customer:id,name,whatsapp_number')
            ->withCount('items')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(string $id): Order
    {
        return Order::query()
            ->with([
                'customer:id,name,whatsapp_number',
                'items',
                'paymentProofs' => fn ($query) => $query->latest(),
                'delivery',
            ])
            ->findOrFail($id);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }
}
