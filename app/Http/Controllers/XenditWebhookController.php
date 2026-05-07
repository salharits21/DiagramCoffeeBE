<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\XenditService;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    protected OrderService $orderService;
    protected XenditService $xenditService;

    public function __construct(OrderService $orderService, XenditService $xenditService)
    {
        $this->orderService = $orderService;
        $this->xenditService = $xenditService;
    }

    /**
     * Handle webhook callback dari Xendit.
     *
     * Xendit mengirimkan notifikasi ke endpoint ini saat
     * status pembayaran berubah (PAID, EXPIRED, dll.).
     */
    public function handle(Request $request)
    {
        // Verifikasi token webhook
        $callbackToken = $request->header('x-callback-token');

        if (!$callbackToken || !$this->xenditService->verifyWebhookToken($callbackToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid callback token',
            ], 403);
        }

        $payload = $request->all();

        $order = $this->orderService->handleXenditCallback($payload);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed',
        ]);
    }
}
