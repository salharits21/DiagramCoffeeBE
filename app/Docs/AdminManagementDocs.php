<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class AdminManagementDocs
{
    // ==========================================
    // 9. Banner Promo
    // ==========================================

    #[OA\Get(
        path: "/banners",
        summary: "Daftar banner promo aktif",
        tags: ["9. Banner Promo"],
        responses: [
            new OA\Response(response: 200, description: "Daftar banner promo")
        ]
    )]
    public function getBanners() {}

    #[OA\Get(
        path: "/admin/banners",
        summary: "Daftar semua banner (Super Admin)",
        tags: ["9. Banner Promo"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Daftar banner promo admin")
        ]
    )]
    public function adminGetBanners() {}

    #[OA\Post(
        path: "/admin/banners",
        summary: "Tambah banner promo baru (Super Admin)",
        tags: ["9. Banner Promo"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["title", "image"],
                    properties: [
                        new OA\Property(property: "title", type: "string", example: "Promo Diskon Akhir Bulan"),
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        new OA\Property(property: "is_active", type: "boolean", example: true)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Banner berhasil dibuat")
        ]
    )]
    public function adminCreateBanner() {}

    #[OA\Put(
        path: "/admin/banners/{banner}",
        summary: "Perbarui banner promo (Super Admin)",
        tags: ["9. Banner Promo"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "banner", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "title", type: "string", example: "Promo Update"),
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        new OA\Property(property: "is_active", type: "boolean", example: true)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Banner berhasil diperbarui")
        ]
    )]
    public function adminUpdateBanner() {}

    #[OA\Delete(
        path: "/admin/banners/{banner}",
        summary: "Hapus banner promo (Super Admin)",
        tags: ["9. Banner Promo"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "banner", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Banner berhasil dihapus")
        ]
    )]
    public function adminDeleteBanner() {}

    // ==========================================
    // 10. Manajemen Admin
    // ==========================================

    #[OA\Get(
        path: "/admin/admins",
        summary: "Daftar akun admin (Super Admin)",
        tags: ["10. Manajemen Admin"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Daftar admin")
        ]
    )]
    public function getAdmins() {}

    #[OA\Get(
        path: "/admin/admins/{admin}",
        summary: "Detail akun admin (Super Admin)",
        tags: ["10. Manajemen Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "admin", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail akun admin")
        ]
    )]
    public function getAdminDetail() {}

    #[OA\Get(
        path: "/admin/branches/{branch}/admins",
        summary: "Daftar admin spesifik per cabang (Super Admin)",
        tags: ["10. Manajemen Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Daftar admin per cabang")
        ]
    )]
    public function getAdminsByBranch() {}

    #[OA\Post(
        path: "/admin/admins",
        summary: "Buat akun admin cabang baru (Super Admin)",
        tags: ["10. Manajemen Admin"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "branch_id"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Admin Setiabudi"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin.setiabudi@diagramcoffee.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Pass1234!"),
                    new OA\Property(property: "branch_id", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Admin cabang berhasil ditambahkan")
        ]
    )]
    public function createAdmin() {}

    #[OA\Put(
        path: "/admin/admins/{admin}",
        summary: "Perbarui akun admin cabang (Super Admin)",
        tags: ["10. Manajemen Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "admin", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Admin Setiabudi Updated"),
                    new OA\Property(property: "branch_id", type: "integer", example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Data akun admin berhasil diperbarui")
        ]
    )]
    public function updateAdmin() {}

    #[OA\Delete(
        path: "/admin/admins/{admin}",
        summary: "Hapus akun admin (Super Admin)",
        tags: ["10. Manajemen Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "admin", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Akun admin berhasil dihapus")
        ]
    )]
    public function deleteAdmin() {}

    // ==========================================
    // 12. Manajemen Pesanan (Admin)
    // ==========================================

    #[OA\Get(
        path: "/admin/orders",
        summary: "Daftar semua pesanan masuk (Admin & Super Admin)",
        tags: ["12. Manajemen Pesanan (Admin)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch_id", in: "query", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Daftar pesanan admin")
        ]
    )]
    public function adminGetOrders() {}

    #[OA\Get(
        path: "/admin/orders/{order}",
        summary: "Detail pesanan admin (Admin & Super Admin)",
        tags: ["12. Manajemen Pesanan (Admin)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail pesanan admin")
        ]
    )]
    public function adminGetOrderDetail() {}

    #[OA\Put(
        path: "/admin/orders/{order}/status",
        summary: "Update status pesanan (Admin & Super Admin)",
        tags: ["12. Manajemen Pesanan (Admin)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["pending", "processing", "completed", "cancelled"], example: "processing")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Status pesanan berhasil diperbarui")
        ]
    )]
    public function adminUpdateOrderStatus() {}

    #[OA\Post(
        path: "/admin/orders/{order}/confirm-cash",
        summary: "Konfirmasi pembayaran tunai (Admin & Super Admin)",
        tags: ["12. Manajemen Pesanan (Admin)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Pembayaran tunai berhasil dikonfirmasi")
        ]
    )]
    public function adminConfirmCashPayment() {}

    // ==========================================
    // 13. Statistik Penjualan
    // ==========================================

    #[OA\Get(
        path: "/admin/statistics",
        summary: "Laporan & statistik penjualan (Admin & Super Admin)",
        tags: ["13. Statistik Penjualan"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "start_date", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "end_date", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Data statistik penjualan")
        ]
    )]
    public function getStatistics() {}
}
