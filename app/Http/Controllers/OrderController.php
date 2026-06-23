<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Buat pesanan baru (Customer only).
     */
    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();
        $guestName = $data['guest_name'] ?? null;

        $order = $this->orderService->createOrder(
            user: $user,
            guestName: $guestName,
            branchId: $data['branch_id'],
            items: $data['items'],
            paymentMethod: $data['payment_method'],
            notes: $data['notes'] ?? null,
            orderType: $data['order_type'],
            tableNumber: $data['table_number'] ?? null,
            userVoucherId: $data['voucher_id'] ?? null,
        );
        
        if ($user) {
            $order->load('items', 'branch', 'user');
        } else {
            $order->load('items', 'branch');
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'data' => $order,
        ], 201);
    }

    /**
     * Preview rincian transaksi sebelum checkout.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'voucher_id' => ['nullable', 'exists:user_vouchers,id'],
        ]);

        $user = $request->user();

        $preview = $this->orderService->previewOrder(
            user: $user,
            branchId: $request->branch_id,
            items: $request->items,
            userVoucherId: $request->voucher_id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Preview transaksi berhasil',
            'data' => $preview,
        ]);
    }

    /**
     * Riwayat pesanan customer yang sedang login.
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('branch', 'items')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pesanan berhasil diambil',
            'data' => $orders,
        ]);
    }

    /**
     * Detail pesanan (customer hanya bisa lihat miliknya).
     */
    public function show(Request $request, int $order)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with('items', 'branch')
            ->findOrFail($order);

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => $order,
        ]);
    }

    /**
     * Cek status pesanan (public).
     */
    public function guestStatus(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'branch'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Cancel pesanan (hanya saat status pending).
     */
    public function cancel(Request $request, int $order)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->findOrFail($order);

        $order = $this->orderService->cancelOrder($order);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan',
            'data' => $order,
        ]);
    }
}
