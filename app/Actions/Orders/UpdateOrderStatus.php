<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Jobs\NotifyCustomerOfOrderStatusChange;
use App\Models\Order;
use App\Repositories\Orders\OrderRepository;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatus
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly SyncDeliveryStatusFromOrder $syncDeliveryStatus,
    ) {}

    public function handle(Order $order, OrderStatus $status): Order
    {
        $statusChanged = $order->status !== $status;

        $order = DB::transaction(function () use ($order, $status) {
            $order = $this->repository->update($order, ['status' => $status]);
            $this->syncDeliveryStatus->handle($order);

            return $order;
        });

        if ($statusChanged) {
            NotifyCustomerOfOrderStatusChange::dispatch($order);
        }

        return $order;
    }
}
