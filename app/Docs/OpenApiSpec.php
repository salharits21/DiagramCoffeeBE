<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Diagram Coffee Backend API Documentation",
    version: "1.0.0",
    description: "Dokumentasi OpenAPI resmi untuk Diagram Coffee Backend Service. Menyediakan panduan lengkap endpoint untuk Aplikasi Customer, Panel Admin, Internal ML Recommendation, dan Xendit Payment Gateway Webhook."
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Server Pengembangan Lokal (Local Development)"
)]
#[OA\Server(
    url: "https://api.diagramcoffee.id/api",
    description: "Server Produksi (Production API)"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Sanctum Token",
    description: "Sanctum Bearer Token. Masukkan format: 'Bearer {access_token}'"
)]
#[OA\SecurityScheme(
    securityScheme: "internalApiKey",
    type: "apiKey",
    in: "header",
    name: "X-Internal-Secret",
    description: "Header kunci rahasia internal untuk Python ML Service"
)]
#[OA\SecurityScheme(
    securityScheme: "xenditWebhookToken",
    type: "apiKey",
    in: "header",
    name: "x-callback-token",
    description: "Token verifikasi callback webhook dari Xendit Payment Gateway"
)]
#[OA\Tag(name: "1. Autentikasi", description: "Registrasi, Login, Password Reset, Logout & Verifikasi Email")]
#[OA\Tag(name: "2. Profil User", description: "Informasi profil user yang sedang login & edit profil")]
#[OA\Tag(name: "3. Cabang (Branch)", description: "Daftar cabang aktif & manajemen lokasi cabang")]
#[OA\Tag(name: "4. Kategori", description: "Kategori produk menu")]
#[OA\Tag(name: "5. Menu Item", description: "Katalog menu master, ekspor & impor CSV")]
#[OA\Tag(name: "6. Menu per Cabang", description: "Ketersediaan menu & stok spesifik per lokasi cabang")]
#[OA\Tag(name: "7. Pesanan (Order)", description: "Checkout order, preview total, cek status guest, & riwayat pesanan")]
#[OA\Tag(name: "8. Voucher & Loyalty", description: "Voucher promo, penukaran poin loyalty, & voucher pengguna")]
#[OA\Tag(name: "9. Banner Promo", description: "Banner promosi pada halaman utama aplikasi")]
#[OA\Tag(name: "10. Manajemen Admin", description: "Pengelolaan akun admin cabang (Khusus Super Admin)")]
#[OA\Tag(name: "11. Manajemen Stok Cabang", description: "Update & pantau stok produk cabang (Admin & Super Admin)")]
#[OA\Tag(name: "12. Manajemen Pesanan (Admin)", description: "Update status pesanan & konfirmasi pembayaran tunai")]
#[OA\Tag(name: "13. Statistik Penjualan", description: "Grafik & laporan performa penjualan")]
#[OA\Tag(name: "14. Rekomendasi Menu", description: "Rekomendasi menu terpopuler & ML recommendation")]
#[OA\Tag(name: "15. Webhook Xendit", description: "Callback update status pembayaran dari Xendit")]
#[OA\Tag(name: "16. Internal API (Python ML)", description: "Integrasi data transaksi untuk Machine Learning Model")]
#[OA\Tag(name: "17. Pengaturan Fee (App Settings)", description: "Konfigurasi biaya layanan & pajak aplikasi")]
class OpenApiSpec
{
}
