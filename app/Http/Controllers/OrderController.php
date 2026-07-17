<?php

namespace App\Http\Controllers;

use App\Actions\Orders\UpdateOrderStatus;
use App\Enums\OrderStatus;
use App\Http\Requests\Orders\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Repositories\Orders\OrderRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly UpdateOrderStatus $updateOrderStatus,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('orders/index', [
            'orders' => $this->repository->paginate(15)->through(
                fn (Order $order) => OrderResource::make($order),
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): Response
    {
        return Inertia::render('orders/show', [
            'order' => OrderResource::make($this->repository->find($order->id)),
        ]);
    }

    /**
     * Update the order's status.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->updateOrderStatus->handle($order, $request->enum('status', OrderStatus::class));

        return back()->with('success', 'Statut de la commande mis à jour.');
    }
}
