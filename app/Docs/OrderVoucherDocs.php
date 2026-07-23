<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class OrderVoucherDocs
{
    // ==========================================
    // 7. Pesanan (Order)
    // ==========================================

    #[OA\Post(
        path: "/orders/preview",
        summary: "Preview rincian total pesanan",
        description: "Menghitung subtotal, pajak, diskon voucher, dan total biaya sebelum checkout.",
        tags: ["7. Pesanan (Order)"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["branch_id", "items"],
                properties: [
                    new OA\Property(property: "branch_id", type: "integer", example: 1),
                    new OA\Property(property: "voucher_code", type: "string", example: "PROMO50"),
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "menu_item_id", type: "integer", example: 2),
                                new OA\Property(property: "quantity", type: "integer", example: 2)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Rincian preview pesanan")
        ]
    )]
    public function previewOrder() {}

    #[OA\Post(
        path: "/orders",
        summary: "Buat pesanan baru (Checkout)",
        description: "Membuat pesanan baru untuk Guest atau Logged-in Customer.",
        tags: ["7. Pesanan (Order)"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["branch_id", "payment_method", "items"],
                properties: [
                    new OA\Property(property: "branch_id", type: "integer", example: 1),
                    new OA\Property(property: "payment_method", type: "string", enum: ["cash", "xendit"], example: "xendit"),
                    new OA\Property(property: "customer_name", type: "string", example: "Ahmad"),
                    new OA\Property(property: "customer_phone", type: "string", example: "0812345678"),
                    new OA\Property(property: "voucher_code", type: "string", example: "PROMO10K"),
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "menu_item_id", type: "integer", example: 1),
                                new OA\Property(property: "quantity", type: "integer", example: 1),
                                new OA\Property(property: "notes", type: "string", example: "Less ice")
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Pesanan berhasil dibuat")
        ]
    )]
    public function createOrder() {}

    #[OA\Get(
        path: "/orders/status/{orderNumber}",
        summary: "Cek status pesanan publik (Guest)",
        tags: ["7. Pesanan (Order)"],
        parameters: [
            new OA\Parameter(name: "orderNumber", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Status pesanan")
        ]
    )]
    public function getGuestOrderStatus() {}

    #[OA\Get(
        path: "/orders",
        summary: "Daftar riwayat pesanan customer (Logged in)",
        tags: ["7. Pesanan (Order)"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Riwayat pesanan")
        ]
    )]
    public function getCustomerOrders() {}

    #[OA\Get(
        path: "/orders/{order}",
        summary: "Detail pesanan customer",
        tags: ["7. Pesanan (Order)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail pesanan")
        ]
    )]
    public function getCustomerOrderDetail() {}

    #[OA\Post(
        path: "/orders/{order}/cancel",
        summary: "Batalkan pesanan customer",
        tags: ["7. Pesanan (Order)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Pesanan berhasil dibatalkan")
        ]
    )]
    public function cancelOrder() {}

    // ==========================================
    // 8. Voucher & Loyalty
    // ==========================================

    #[OA\Get(
        path: "/vouchers",
        summary: "Daftar voucher publik / loyalty catalog",
        tags: ["8. Voucher & Loyalty"],
        responses: [
            new OA\Response(response: 200, description: "Katalog voucher")
        ]
    )]
    public function getVouchers() {}

    #[OA\Get(
        path: "/vouchers/my-vouchers",
        summary: "Daftar voucher milik customer",
        tags: ["8. Voucher & Loyalty"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Daftar voucher tersimpan milik user")
        ]
    )]
    public function getMyVouchers() {}

    #[OA\Post(
        path: "/vouchers/exchange",
        summary: "Tukar poin loyalty dengan voucher",
        tags: ["8. Voucher & Loyalty"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["voucher_id"],
                properties: [
                    new OA\Property(property: "voucher_id", type: "integer", example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Penukaran voucher berhasil")
        ]
    )]
    public function exchangeVoucher() {}

    #[OA\Post(
        path: "/admin/vouchers",
        summary: "Buat voucher baru (Super Admin)",
        tags: ["8. Voucher & Loyalty"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["code", "name", "discount_type", "discount_value", "min_order_amount"],
                properties: [
                    new OA\Property(property: "code", type: "string", example: "PROMO50K"),
                    new OA\Property(property: "name", type: "string", example: "Voucher Diskon 50K"),
                    new OA\Property(property: "discount_type", type: "string", enum: ["fixed", "percent"], example: "fixed"),
                    new OA\Property(property: "discount_value", type: "number", example: 50000),
                    new OA\Property(property: "min_order_amount", type: "number", example: 100000),
                    new OA\Property(property: "points_required", type: "integer", example: 100)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Voucher berhasil dibuat")
        ]
    )]
    public function adminCreateVoucher() {}

    #[OA\Put(
        path: "/admin/vouchers/{voucher}",
        summary: "Perbarui voucher (Super Admin)",
        tags: ["8. Voucher & Loyalty"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "voucher", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Voucher Diskon 50K Updated"),
                    new OA\Property(property: "discount_value", type: "number", example: 55000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Voucher berhasil diperbarui")
        ]
    )]
    public function adminUpdateVoucher() {}

    #[OA\Delete(
        path: "/admin/vouchers/{voucher}",
        summary: "Hapus voucher (Super Admin)",
        tags: ["8. Voucher & Loyalty"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "voucher", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Voucher berhasil dihapus")
        ]
    )]
    public function adminDeleteVoucher() {}
}
