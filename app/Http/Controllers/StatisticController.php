<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    /**
     * Menampilkan statistik penjualan.
     *
     * Query params:
     * - days: jumlah hari untuk data harian & top menu (default 7, max 30)
     * - branch_id: filter cabang (super_admin only, opsional)
     */
    public function index(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:30',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = $request->user();
        $days = (int) ($request->days ?? 7);

        // Tentukan branch_id berdasarkan role
        $branchId = null;
        if ($user->role === 'admin') {
            // Admin hanya bisa melihat cabangnya sendiri
            $branchId = $user->branch_id;
        } elseif ($user->role === 'super_admin' && $request->branch_id) {
            // Super admin bisa filter per cabang, atau lihat semua
            $branchId = (int) $request->branch_id;
        }

        $today = Carbon::today();

        // ==========================================
        // 1. Jumlah transaksi hari ini
        // ==========================================
        $todayTransactionsCount = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        // ==========================================
        // 2. Total pendapatan hari ini
        // ==========================================
        $todayRevenue = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // ==========================================
        // 3. Total pemasukan harian dalam N hari terakhir
        // ==========================================
        $startDate = Carbon::today()->subDays($days - 1);

        $dailyRevenue = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // ==========================================
        // 4. Top 5 Menu dalam N hari terakhir
        // ==========================================
        $topMenus = OrderItem::whereHas('order', function ($q) use ($startDate, $branchId) {
                $q->where('status', 'completed')
                  ->whereDate('created_at', '>=', $startDate)
                  ->when($branchId, fn ($q2) => $q2->where('branch_id', $branchId));
            })
            ->select(
                'menu_item_id',
                'menu_item_name',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(subtotal) as total_sales')
            )
            ->groupBy('menu_item_id', 'menu_item_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Statistik penjualan berhasil diambil',
            'data' => [
                'today_transactions' => $todayTransactionsCount,
                'today_revenue' => $todayRevenue,
                'daily_revenue' => $dailyRevenue,
                'top_menus' => $topMenus,
            ],
        ]);
    }
}
