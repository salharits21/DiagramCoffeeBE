<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Daftar semua pesanan (Super Admin: semua, Admin: hanya cabangnya).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with('user', 'branch', 'items');

        // Admin hanya bisa lihat pesanan cabangnya
        if ($user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment_status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by branch (super admin)
        if ($request->has('branch_id') && $user->isSuperAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $orders,
        ]);
    }

    /**
     * Detail pesanan.
     */
    public function show(Request $request, int $order)
    {
        $user = $request->user();
        $query = Order::with('user', 'branch', 'items');

        // Admin hanya bisa lihat pesanan cabangnya
        if ($user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        $order = $query->findOrFail($order);

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => $order,
        ]);
    }

    /**
     * Update status pesanan: confirmed → preparing → ready → completed
     */
    public function updateStatus(UpdateOrderStatusRequest $request, int $order)
    {
        $user = $request->user();
        $query = Order::query();

        if ($user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        $order = $query->findOrFail($order);

        $order = $this->orderService->updateStatus($order, $request->validated()['status']);

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui',
            'data' => $order,
        ]);
    }

    /**
     * Konfirmasi pembayaran tunai.
     */
    public function confirmCash(Request $request, int $order)
    {
        $user = $request->user();
        $query = Order::query();

        if ($user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        $order = $query->findOrFail($order);

        $order = $this->orderService->confirmCashPayment($order);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran tunai berhasil dikonfirmasi',
            'data' => $order,
        ]);
    }
}
