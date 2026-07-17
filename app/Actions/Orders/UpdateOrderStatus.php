<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Orders\OrderRepository;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatus
{
    public function __construct(
        private readonly OrderRepository $repository,
    ) {}

    public function handle(Order $order, OrderStatus $status): Order
    {
        return DB::transaction(fn () => $this->repository->update($order, ['status' => $status]));
    }
}
