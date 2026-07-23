<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class SystemIntegrationsDocs
{
    // ==========================================
    // 14. Rekomendasi Menu
    // ==========================================

    #[OA\Get(
        path: "/recommendations",
        summary: "Rekomendasi menu terpopuler & ML recommendation",
        description: "Mengambil daftar menu rekomendasi berbasis algoritma ML Python atau statistik popularitas.",
        tags: ["14. Rekomendasi Menu"],
        parameters: [
            new OA\Parameter(name: "branch_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Daftar rekomendasi menu")
        ]
    )]
    public function getRecommendations() {}

    // ==========================================
    // 15. Webhook Xendit
    // ==========================================

    #[OA\Post(
        path: "/webhooks/xendit",
        summary: "Callback notification status pembayaran dari Xendit",
        description: "Endpoint publik yang dipanggil oleh Xendit untuk memperbarui status pesanan menjadi paid/failed.",
        tags: ["15. Webhook Xendit"],
        security: [["xenditWebhookToken" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["id", "external_id", "status"],
                properties: [
                    new OA\Property(property: "id", type: "string", example: "5f8d..."),
                    new OA\Property(property: "external_id", type: "string", example: "ORD-20260723-001"),
                    new OA\Property(property: "status", type: "string", example: "PAID"),
                    new OA\Property(property: "paid_amount", type: "number", example: 55000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Webhook berhasil diproses")
        ]
    )]
    public function handleXenditWebhook() {}

    // ==========================================
    // 16. Internal API (Python ML)
    // ==========================================

    #[OA\Get(
        path: "/internal/transactions",
        summary: "Export transaksi untuk training model ML Python",
        description: "Endpoint terproteksi khusus untuk pertukaran data transaksi dengan Service Python ML.",
        tags: ["16. Internal API (Python ML)"],
        security: [["internalApiKey" => []]],
        responses: [
            new OA\Response(response: 200, description: "Data transaksi terstruktur untuk model ML")
        ]
    )]
    public function exportInternalTransactions() {}

    // ==========================================
    // 17. Pengaturan Fee (App Settings)
    // ==========================================

    #[OA\Get(
        path: "/fee",
        summary: "Ambil pengaturan biaya aplikasi (Public)",
        tags: ["17. Pengaturan Fee (App Settings)"],
        responses: [
            new OA\Response(response: 200, description: "Pengaturan fee & pajak aplikasi")
        ]
    )]
    public function getPublicFee() {}

    #[OA\Get(
        path: "/admin/settings/fee",
        summary: "Daftar biaya aplikasi (Super Admin)",
        tags: ["17. Pengaturan Fee (App Settings)"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Daftar pengaturan fee admin")
        ]
    )]
    public function adminGetFeeSettings() {}

    #[OA\Post(
        path: "/admin/settings/fee",
        summary: "Tambah biaya aplikasi baru (Super Admin)",
        tags: ["17. Pengaturan Fee (App Settings)"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["key", "name", "amount", "type"],
                properties: [
                    new OA\Property(property: "key", type: "string", example: "service_fee"),
                    new OA\Property(property: "name", type: "string", example: "Biaya Layanan"),
                    new OA\Property(property: "amount", type: "number", example: 2000),
                    new OA\Property(property: "type", type: "string", enum: ["fixed", "percent"], example: "fixed")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Biaya aplikasi berhasil dibuat")
        ]
    )]
    public function adminCreateFeeSetting() {}

    #[OA\Put(
        path: "/admin/settings/fee/{key}",
        summary: "Perbarui pengaturan biaya aplikasi (Super Admin)",
        tags: ["17. Pengaturan Fee (App Settings)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "key", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "amount", "type"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Biaya Layanan Updated"),
                    new OA\Property(property: "amount", type: "number", example: 2500),
                    new OA\Property(property: "type", type: "string", enum: ["fixed", "percent"], example: "fixed")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Biaya aplikasi berhasil diperbarui")
        ]
    )]
    public function adminUpdateFeeSetting() {}

    #[OA\Delete(
        path: "/admin/settings/fee/{key}",
        summary: "Hapus pengaturan biaya aplikasi (Super Admin)",
        tags: ["17. Pengaturan Fee (App Settings)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "key", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Biaya aplikasi berhasil dihapus")
        ]
    )]
    public function adminDeleteFeeSetting() {}
}
