<?php

namespace App\Http\Controllers;

use App\Actions\Addresses\CreateAddress;
use App\Actions\Addresses\DeleteAddress;
use App\Actions\Addresses\UpdateAddress;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Customer;
use App\Repositories\Addresses\AddressRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function __construct(
        private readonly AddressRepository $repository,
        private readonly CreateAddress $createAddress,
        private readonly UpdateAddress $updateAddress,
        private readonly DeleteAddress $deleteAddress,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('addresses/index', [
            'addresses' => $this->repository->paginate(15)->through(
                fn (Address $address) => AddressResource::make($address),
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('addresses/create', [
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name', 'whatsapp_number']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $this->createAddress->handle($request->validated());

        return to_route('addresses.index')->with('success', 'Adresse créée.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address): Response
    {
        return Inertia::render('addresses/edit', [
            'address' => AddressResource::make($this->repository->find($address->id)),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name', 'whatsapp_number']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $this->updateAddress->handle($address, $request->validated());

        return to_route('addresses.index')->with('success', 'Adresse mise à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address): RedirectResponse
    {
        $this->deleteAddress->handle($address);

        return to_route('addresses.index')->with('success', 'Adresse supprimée.');
    }
}
