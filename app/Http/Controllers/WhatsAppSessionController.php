<?php

namespace App\Http\Controllers;

use App\Actions\WhatsAppSessions\ConnectWhatsAppSession;
use App\Actions\WhatsAppSessions\DisconnectWhatsAppSession;
use App\Actions\WhatsAppSessions\RefreshWhatsAppSessionStatus;
use App\Enums\WhatsAppSessionStatus;
use App\Http\Requests\WhatsAppSessions\StoreWhatsAppSessionRequest;
use App\Http\Resources\WhatsAppSessionResource;
use App\Models\WhatsAppSession;
use App\Repositories\WhatsAppSessions\WhatsAppSessionRepository;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class WhatsAppSessionController extends Controller
{
    public function __construct(
        private readonly WhatsAppSessionRepository $repository,
        private readonly ConnectWhatsAppSession $connectWhatsAppSession,
        private readonly RefreshWhatsAppSessionStatus $refreshWhatsAppSessionStatus,
        private readonly DisconnectWhatsAppSession $disconnectWhatsAppSession,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        foreach ($this->repository->all() as $session) {
            $hasIncompleteConnectedProfile = $session->status === WhatsAppSessionStatus::Working
                && (blank($session->profile_name) || str_contains($session->phone_number ?? '', '@'));

            if (! $session->status->isPending() && ! $hasIncompleteConnectedProfile) {
                continue;
            }

            try {
                $this->refreshWhatsAppSessionStatus->handle($session);
            } catch (RequestException $exception) {
                Log::warning('Unable to refresh WhatsApp session status from WAHA.', [
                    'session' => $session->waha_session_name,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return Inertia::render('whatsapp-sessions/index', [
            'sessions' => WhatsAppSessionResource::collection($this->repository->all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWhatsAppSessionRequest $request): RedirectResponse
    {
        $this->connectWhatsAppSession->handle($request->string('label')->toString());

        return back()->with('success', 'Session WhatsApp créée, scannez le QR code.');
    }

    /**
     * Refresh the connection status of the specified resource.
     */
    public function refresh(WhatsAppSession $whatsAppSession): RedirectResponse
    {
        $this->refreshWhatsAppSessionStatus->handle($whatsAppSession);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WhatsAppSession $whatsAppSession): RedirectResponse
    {
        $this->disconnectWhatsAppSession->handle($whatsAppSession);

        return back()->with('success', 'Session WhatsApp déconnectée.');
    }
}
