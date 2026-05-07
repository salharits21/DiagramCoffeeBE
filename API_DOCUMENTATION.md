# Diagram Coffee — API Documentation

> **Base URL**: `http://localhost:8000/api`  
> **Authentication**: Laravel Sanctum (Session-based with CSRF / Bearer Token)  
> **Semua password seeder**: `@Password123`

---

## Daftar Akun Seeder

| Role | Email | Password |
|---|---|---|
| Super Admin | `superadmin@diagramcoffee.com` | `@Password123` |
| Admin (Dago) | `admin.dago@diagramcoffee.com` | `@Password123` |
| Admin (Braga) | `admin.braga@diagramcoffee.com` | `@Password123` |
| Admin (Paskal) | `admin.paskal@diagramcoffee.com` | `@Password123` |
| Customer | `customer@example.com` | `@Password123` |
| Customer | `jane@example.com` | `@Password123` |

---

## Response Format

Semua endpoint mengembalikan format JSON yang konsisten:

```json
{
  "success": true,
  "message": "Pesan deskriptif",
  "data": { }
}
```

Error validation (422):
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

## 1. Authentication

### POST `/register`
Registrasi customer baru.

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `name` | string | ✅ | Nama lengkap |
| `email` | string | ✅ | Email unik |
| `password` | string | ✅ | Min 8 karakter |
| `password_confirmation` | string | ✅ | Harus sama |

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": { "id": 1, "name": "...", "email": "...", "role": "customer" },
    "access_token": "1|abc...",
    "token_type": "Bearer"
  }
}
```

---

### POST `/login`
Login user (semua role).

**Body:**
| Field | Type | Required |
|---|---|---|
| `email` | string | ✅ |
| `password` | string | ✅ |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": { "id": 1, "name": "...", "email": "...", "role": "super_admin" }
}
```

**Error:** `401 Unauthorized` — Email atau password salah

---

### POST `/logout` 🔒
Logout user.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

---

### GET `/user` 🔒
Mendapatkan data user yang sedang login.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

---

## 2. Branches (Cabang)

### GET `/branches`
Daftar semua cabang **aktif** (public).

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Daftar cabang berhasil diambil",
  "data": [
    {
      "id": 1,
      "name": "Diagram Coffee Dago",
      "address": "Jl. Ir. H. Juanda No.123, Dago, Bandung",
      "phone": "022-1234567",
      "status": "active",
      "opening_time": "08:00",
      "closing_time": "22:00"
    }
  ]
}
```

---

### GET `/branches/{id}`
Detail cabang tertentu.

**Response:** `200 OK`

---

### GET `/admin/branches` 🔒 `Super Admin`
Semua cabang termasuk yang **inactive**.

---

### POST `/admin/branches` 🔒 `Super Admin`
Buat cabang baru.

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `name` | string | ✅ | Nama cabang |
| `address` | string | ✅ | Alamat lengkap |
| `phone` | string | ❌ | Nomor telepon |
| `status` | enum | ❌ | `active` (default) / `inactive` |
| `opening_time` | time | ❌ | Format `HH:mm` |
| `closing_time` | time | ❌ | Format `HH:mm` |

**Response:** `201 Created`

---

### PUT `/admin/branches/{id}` 🔒 `Super Admin`
Update cabang.

**Body:** Sama seperti POST (semua field optional).

**Response:** `200 OK`

---

### DELETE `/admin/branches/{id}` 🔒 `Super Admin`
Hapus cabang (soft delete).

**Response:** `200 OK`

---

## 3. Categories (Kategori Menu)

### GET `/categories`
Daftar semua kategori beserta jumlah menu aktif.

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Coffee",
      "slug": "coffee",
      "description": "Berbagai pilihan kopi berkualitas",
      "sort_order": 1,
      "menu_items_count": 10
    }
  ]
}
```

---

### GET `/categories/{id}`
Detail kategori beserta daftar menu aktifnya.

**Response:** `200 OK` — Includes `menu_items` array

---

### POST `/admin/categories` 🔒 `Super Admin`
Buat kategori baru. Slug otomatis di-generate dari `name`.

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `name` | string | ✅ | Nama kategori |
| `description` | string | ❌ | Deskripsi |
| `sort_order` | integer | ❌ | Urutan tampil (default: 0) |

**Response:** `201 Created`

---

### PUT `/admin/categories/{id}` 🔒 `Super Admin`
Update kategori. Slug otomatis di-regenerate jika `name` berubah.

**Response:** `200 OK`

---

### DELETE `/admin/categories/{id}` 🔒 `Super Admin`
Hapus kategori (soft delete).

**Response:** `200 OK`

---

## 4. Menu Items

### GET `/menu-items`
Daftar menu. Customer hanya melihat menu aktif. Super Admin/Admin melihat semua.

**Query Parameters:**
| Param | Type | Keterangan |
|---|---|---|
| `category_id` | integer | Filter by kategori |
| `search` | string | Cari berdasarkan nama |

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "Espresso",
      "slug": "espresso",
      "description": "Kopi espresso murni dengan crema sempurna",
      "base_price": "22000.00",
      "image_url": null,
      "is_active": true,
      "category": { "id": 1, "name": "Coffee", "slug": "coffee" }
    }
  ]
}
```

---

### GET `/menu-items/{id}`
Detail menu beserta ketersediaan di semua cabang.

**Response:** `200 OK` — Includes `branches` array with pivot data

---

### GET `/branches/{id}/menu`
Menu yang **tersedia** (available) di cabang tertentu. Berguna untuk customer saat memilih cabang.

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Cappuccino",
      "base_price": "30000.00",
      "stock": 25,
      "is_available": true,
      "is_promo_active": true,
      "discount_type": "percentage",
      "discount_percentage": "15.00",
      "discount_amount": null,
      "category": { "id": 1, "name": "Coffee" }
    }
  ]
}
```

---

### POST `/admin/menu-items` 🔒 `Super Admin`
Buat menu baru. Slug otomatis di-generate.

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `category_id` | integer | ✅ | ID kategori (harus valid) |
| `name` | string | ✅ | Nama menu |
| `description` | string | ❌ | Deskripsi menu |
| `base_price` | numeric | ✅ | Harga dasar (Rupiah) |
| `image_url` | string | ❌ | URL gambar |
| `is_active` | boolean | ❌ | Default: `true` |

**Response:** `201 Created`

---

### PUT `/admin/menu-items/{id}` 🔒 `Super Admin`
Update menu item.

**Response:** `200 OK`

---

### DELETE `/admin/menu-items/{id}` 🔒 `Super Admin`
Hapus menu (soft delete).

**Response:** `200 OK`

---

## 5. Stock & Promo per Cabang

### GET `/admin/branches/{branchId}/stock` 🔒 `Super Admin, Admin`
Lihat semua menu beserta stok & promo di cabang tertentu.

> ⚠️ **Admin** hanya bisa mengakses data **cabangnya sendiri**.

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "menu_item_id": 3,
      "branch_id": 1,
      "is_available": true,
      "stock": 25,
      "discount_type": "percentage",
      "discount_percentage": "15.00",
      "discount_amount": null,
      "is_promo_active": true,
      "menu_item": {
        "id": 3,
        "name": "Cappuccino",
        "base_price": "30000.00",
        "category": { "id": 1, "name": "Coffee" }
      }
    }
  ]
}
```

---

### POST `/admin/branches/{branchId}/menu-items/{menuItemId}` 🔒 `Super Admin`
Assign menu ke cabang (buat record stok baru).

**Response:** `201 Created`  
**Error:** `409 Conflict` — Menu sudah ada di cabang

---

### PUT `/admin/branches/{branchId}/menu-items/{menuItemId}/stock` 🔒 `Super Admin, Admin`
Update stok, ketersediaan, dan promo menu di cabang.

> ⚠️ **Admin** hanya bisa update **cabangnya sendiri**.

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `is_available` | boolean | ❌ | Toggle ketersediaan |
| `stock` | integer\|null | ❌ | Jumlah stok. `null` = unlimited |
| `discount_type` | enum\|null | ❌ | `percentage`, `fixed`, atau `null` (hapus promo) |
| `discount_percentage` | numeric | Conditional | Wajib jika `discount_type=percentage`. Range: 0-100 |
| `discount_amount` | numeric | Conditional | Wajib jika `discount_type=fixed`. Nominal potongan (Rp) |
| `is_promo_active` | boolean | ❌ | Toggle aktif/nonaktif promo |

> **Catatan**: Hanya **satu tipe diskon** yang bisa aktif. Saat `discount_type` berubah, field diskon yang tidak relevan otomatis di-reset ke `null`.

**Contoh — Set diskon persentase:**
```json
{
  "discount_type": "percentage",
  "discount_percentage": 15,
  "is_promo_active": true
}
```

**Contoh — Set potongan langsung:**
```json
{
  "discount_type": "fixed",
  "discount_amount": 5000,
  "is_promo_active": true
}
```

**Contoh — Hapus promo:**
```json
{
  "discount_type": null,
  "is_promo_active": false
}
```

**Response:** `200 OK`

---

### DELETE `/admin/branches/{branchId}/menu-items/{menuItemId}` 🔒 `Super Admin`
Unassign menu dari cabang.

**Response:** `200 OK`  
**Error:** `404 Not Found` — Menu tidak ditemukan di cabang

---

## 6. Manajemen Admin

### GET `/admin/admins` 🔒 `Super Admin`
Daftar semua admin beserta data cabangnya.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Daftar admin berhasil diambil",
  "data": [
    {
      "id": 2,
      "name": "Admin Dago",
      "email": "admin.dago@diagramcoffee.com",
      "role": "admin",
      "branch_id": 1,
      "loyalty_points": 0,
      "branch": {
        "id": 1,
        "name": "Diagram Coffee Dago",
        "address": "Jl. Ir. H. Juanda No.123, Dago, Bandung"
      }
    }
  ]
}
```

---

### GET `/admin/admins/{id}` 🔒 `Super Admin`
Detail admin tertentu.

**Response:** `200 OK`  
**Error:** `404 Not Found` — Admin tidak ditemukan (atau ID bukan role admin)

---

### POST `/admin/admins` 🔒 `Super Admin`
Buat admin baru dan assign ke cabang.

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `name` | string | ✅ | Nama lengkap admin |
| `email` | string | ✅ | Email unik |
| `password` | string | ✅ | Min 8 karakter |
| `password_confirmation` | string | ✅ | Harus sama dengan password |
| `branch_id` | integer | ✅ | ID cabang (harus valid) |

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Admin berhasil ditambahkan",
  "data": {
    "id": 7,
    "name": "Admin Baru",
    "email": "admin.baru@diagramcoffee.com",
    "role": "admin",
    "branch_id": 2,
    "branch": { "id": 2, "name": "Diagram Coffee Braga" }
  }
}
```

> **Catatan**: Role otomatis di-set ke `admin`. Tidak bisa membuat Super Admin dari endpoint ini.

---

### PUT `/admin/admins/{id}` 🔒 `Super Admin`
Update data admin (nama, email, password, cabang).

**Body:** (semua field optional)
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `name` | string | ❌ | Nama baru |
| `email` | string | ❌ | Email baru (unique) |
| `password` | string | ❌ | Password baru (min 8 karakter) |
| `password_confirmation` | string | Conditional | Wajib jika `password` diisi |
| `branch_id` | integer | ❌ | Pindahkan admin ke cabang lain |

**Contoh — Pindah cabang:**
```json
{
  "branch_id": 3
}
```

**Response:** `200 OK`  
**Error:** `404 Not Found` — Admin tidak ditemukan

---

### DELETE `/admin/admins/{id}` 🔒 `Super Admin`
Hapus akun admin (hard delete). Semua token otomatis di-revoke.

**Response:** `200 OK`  
**Error:** `404 Not Found` — Admin tidak ditemukan

> ⚠️ Ini adalah **hard delete**, data admin akan dihapus permanen.

---

## 7. Pemesanan (Customer Orders)

### POST `/orders` 🔒 `Customer`
Buat pesanan baru.

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `branch_id` | integer | ✅ | Cabang tujuan |
| `payment_method` | enum | ✅ | `xendit` (QRIS/E-wallet) atau `cash` (tunai) |
| `notes` | string | ❌ | Catatan pesanan (max 500 karakter) |
| `items` | array | ✅ | Minimal 1 item |
| `items.*.menu_item_id` | integer | ✅ | ID menu item |
| `items.*.quantity` | integer | ✅ | Jumlah (1-100) |
| `items.*.notes` | string | ❌ | Catatan per item ("less sugar", dll.) |

**Contoh request:**
```json
{
  "branch_id": 1,
  "payment_method": "xendit",
  "notes": "Takeaway",
  "items": [
    { "menu_item_id": 1, "quantity": 2 },
    { "menu_item_id": 5, "quantity": 1, "notes": "extra shot" }
  ]
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Pesanan berhasil dibuat",
  "data": {
    "id": 1,
    "order_number": "ORD-20260507-A1B2C",
    "status": "pending",
    "payment_method": "xendit",
    "payment_status": "unpaid",
    "xendit_invoice_url": "https://checkout.xendit.co/web/...",
    "subtotal": "85000.00",
    "discount_total": "5000.00",
    "total_amount": "80000.00",
    "loyalty_points_earned": 8,
    "items": [
      {
        "menu_item_id": 1,
        "menu_item_name": "Espresso",
        "quantity": 2,
        "unit_price": "22500.00",
        "subtotal": "45000.00"
      }
    ]
  }
}
```

> **Catatan penting:**
> - Stok dikurangi saat order dibuat
> - Harga final sudah termasuk diskon promo cabang
> - Loyalty points: **1 poin per Rp 10.000** (dibulatkan ke bawah)
> - Jika `payment_method=xendit`, response menyertakan `xendit_invoice_url` untuk redirect customer ke halaman pembayaran

---

### GET `/orders` 🔒 `Customer`
Riwayat pesanan customer yang sedang login.

**Response:** `200 OK` — Array of orders

---

### GET `/orders/{id}` 🔒 `Customer`
Detail pesanan (hanya bisa lihat pesanan sendiri).

**Response:** `200 OK`  
**Error:** `404 Not Found` — Pesanan bukan miliknya

---

### POST `/orders/{id}/cancel` 🔒 `Customer`
Batalkan pesanan. **Hanya bisa saat status `pending`.**

**Response:** `200 OK`

> Stok yang dikurangi akan dikembalikan saat cancel.

**Error:** `422 Unprocessable` — Status bukan `pending`

---

## 8. Manajemen Pesanan (Admin)

### GET `/admin/orders` 🔒 `Super Admin, Admin`
Daftar pesanan. Admin hanya melihat pesanan cabangnya.

**Query Parameters:**
| Param | Type | Keterangan |
|---|---|---|
| `status` | enum | Filter: `pending`, `confirmed`, `preparing`, `ready`, `completed`, `cancelled` |
| `payment_status` | enum | Filter: `unpaid`, `paid`, `failed`, `expired`, `refunded` |
| `branch_id` | integer | Filter by cabang (Super Admin only) |

---

### GET `/admin/orders/{id}` 🔒 `Super Admin, Admin`
Detail pesanan.

---

### PUT `/admin/orders/{id}/status` 🔒 `Super Admin, Admin`
Update status pesanan: `confirmed → preparing → ready → completed`

**Body:**
| Field | Type | Required | Keterangan |
|---|---|---|---|
| `status` | enum | ✅ | `preparing`, `ready`, atau `completed` |

**Error:** `422 Unprocessable` — Transisi status tidak valid

---

### POST `/admin/orders/{id}/confirm-cash` 🔒 `Super Admin, Admin`
Konfirmasi pembayaran tunai oleh kasir. Loyalty points otomatis ditambahkan.

**Error:** `422` — Bukan cash / sudah dibayar

---

## 9. Xendit Webhook

### POST `/webhooks/xendit`
Endpoint untuk menerima notifikasi pembayaran dari Xendit. **Public** tetapi diverifikasi oleh `x-callback-token`.

| Status Xendit | Aksi |
|---|---|
| `PAID` / `SETTLED` | Order → `confirmed`, loyalty points ditambahkan |
| `EXPIRED` | Order → `cancelled`, stok dikembalikan |

> Webhook bersifat **idempotent** — callback ganda aman.

---

## Order Status Flow

```
pending → confirmed → preparing → ready → completed
   ↓                                         
cancelled (by customer/expired payment)
```

---

## Environment Setup

Tambahkan ke file `.env`:
```
XENDIT_SECRET_KEY=xnd_development_xxx
XENDIT_WEBHOOK_SECRET=xxx
```

> Dapatkan dari [dashboard.xendit.co](https://dashboard.xendit.co/settings/developers)

---

## Role & Access Control

| Endpoint | Super Admin | Admin | Customer | Public |
|---|:---:|:---:|:---:|:---:|
| `GET /branches` | ✅ | ✅ | ✅ | ✅ |
| `GET /categories` | ✅ | ✅ | ✅ | ✅ |
| `GET /menu-items` | ✅ (semua) | ✅ (aktif) | ✅ (aktif) | ✅ (aktif) |
| `GET /branches/{id}/menu` | ✅ | ✅ | ✅ | ✅ |
| CRUD `/admin/branches` | ✅ | ❌ | ❌ | ❌ |
| CRUD `/admin/categories` | ✅ | ❌ | ❌ | ❌ |
| CRUD `/admin/menu-items` | ✅ | ❌ | ❌ | ❌ |
| Assign/Unassign menu-cabang | ✅ | ❌ | ❌ | ❌ |
| CRUD `/admin/admins` | ✅ | ❌ | ❌ | ❌ |
| `GET /admin/branches/{id}/stock` | ✅ | ✅ (own) | ❌ | ❌ |
| `PUT .../stock` | ✅ | ✅ (own) | ❌ | ❌ |
| `POST /orders` | ❌ | ❌ | ✅ | ❌ |
| `GET /orders` | ❌ | ❌ | ✅ (own) | ❌ |
| `POST /orders/{id}/cancel` | ❌ | ❌ | ✅ (own) | ❌ |
| `/admin/orders` | ✅ | ✅ (own branch) | ❌ | ❌ |
| `.../confirm-cash` | ✅ | ✅ (own branch) | ❌ | ❌ |

> 🔒 = Membutuhkan autentikasi (Bearer Token / Session)  
> ✅ (own) = Customer hanya pesanan sendiri, Admin hanya cabangnya

---

## HTTP Status Codes

| Code | Keterangan |
|---|---|
| `200` | Berhasil |
| `201` | Resource berhasil dibuat |
| `401` | Unauthorized — belum login |
| `403` | Forbidden — tidak punya akses |
| `404` | Resource tidak ditemukan |
| `409` | Conflict — resource sudah ada |
| `422` | Validation error |

