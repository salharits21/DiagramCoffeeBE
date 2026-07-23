<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class BranchCategoryMenuDocs
{
    // ==========================================
    // 3. Cabang (Branch)
    // ==========================================

    #[OA\Get(
        path: "/branches",
        summary: "Daftar cabang aktif",
        tags: ["3. Cabang (Branch)"],
        responses: [
            new OA\Response(response: 200, description: "Daftar cabang berhasil diambil")
        ]
    )]
    public function getBranches() {}

    #[OA\Get(
        path: "/branches/{branch}",
        summary: "Detail cabang",
        tags: ["3. Cabang (Branch)"],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail cabang")
        ]
    )]
    public function getBranchDetail() {}

    #[OA\Get(
        path: "/admin/branches",
        summary: "Daftar semua cabang (Super Admin)",
        tags: ["3. Cabang (Branch)"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Daftar cabang admin")
        ]
    )]
    public function adminGetBranches() {}

    #[OA\Post(
        path: "/admin/branches",
        summary: "Tambah cabang baru (Super Admin)",
        tags: ["3. Cabang (Branch)"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "address", "phone"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Cabang Setiabudi"),
                    new OA\Property(property: "address", type: "string", example: "Jl. Setiabudi No. 123"),
                    new OA\Property(property: "phone", type: "string", example: "08123456789")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Cabang berhasil ditambahkan")
        ]
    )]
    public function adminCreateBranch() {}

    #[OA\Put(
        path: "/admin/branches/{branch}",
        summary: "Perbarui data cabang (Super Admin)",
        tags: ["3. Cabang (Branch)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "address", "phone"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Cabang Setiabudi Updated"),
                    new OA\Property(property: "address", type: "string", example: "Jl. Setiabudi No. 456"),
                    new OA\Property(property: "phone", type: "string", example: "08123456789")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Cabang berhasil diperbarui")
        ]
    )]
    public function adminUpdateBranch() {}

    #[OA\Delete(
        path: "/admin/branches/{branch}",
        summary: "Hapus cabang (Super Admin)",
        tags: ["3. Cabang (Branch)"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Cabang berhasil dihapus")
        ]
    )]
    public function adminDeleteBranch() {}

    // ==========================================
    // 4. Kategori
    // ==========================================

    #[OA\Get(
        path: "/categories",
        summary: "Daftar kategori menu",
        tags: ["4. Kategori"],
        responses: [
            new OA\Response(response: 200, description: "Daftar kategori")
        ]
    )]
    public function getCategories() {}

    #[OA\Get(
        path: "/categories/{category}",
        summary: "Detail kategori menu",
        tags: ["4. Kategori"],
        parameters: [
            new OA\Parameter(name: "category", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail kategori")
        ]
    )]
    public function getCategoryDetail() {}

    #[OA\Post(
        path: "/admin/categories",
        summary: "Tambah kategori baru (Super Admin)",
        tags: ["4. Kategori"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Espresso Based")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Kategori berhasil dibuat")
        ]
    )]
    public function adminCreateCategory() {}

    #[OA\Put(
        path: "/admin/categories/{category}",
        summary: "Perbarui kategori (Super Admin)",
        tags: ["4. Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "category", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Manual Brew")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Kategori berhasil diperbarui")
        ]
    )]
    public function adminUpdateCategory() {}

    #[OA\Delete(
        path: "/admin/categories/{category}",
        summary: "Hapus kategori (Super Admin)",
        tags: ["4. Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "category", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Kategori berhasil dihapus")
        ]
    )]
    public function adminDeleteCategory() {}

    // ==========================================
    // 5. Menu Item Master
    // ==========================================

    #[OA\Get(
        path: "/menu-items",
        summary: "Daftar semua menu item master",
        tags: ["5. Menu Item"],
        responses: [
            new OA\Response(response: 200, description: "Daftar menu master")
        ]
    )]
    public function getMenuItems() {}

    #[OA\Get(
        path: "/menu-items/{menuItem}",
        summary: "Detail menu item master",
        tags: ["5. Menu Item"],
        parameters: [
            new OA\Parameter(name: "menuItem", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail menu master")
        ]
    )]
    public function getMenuItemDetail() {}

    #[OA\Get(
        path: "/admin/menu-items/export",
        summary: "Ekspor menu item ke CSV (Super Admin)",
        tags: ["5. Menu Item"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "File CSV menu item")
        ]
    )]
    public function exportMenuItems() {}

    #[OA\Post(
        path: "/admin/menu-items",
        summary: "Buat menu item baru (Super Admin)",
        tags: ["5. Menu Item"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["category_id", "name", "price", "image"],
                    properties: [
                        new OA\Property(property: "category_id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "Kopi Susu Gula Aren"),
                        new OA\Property(property: "description", type: "string", example: "Espresso campur gula aren murni"),
                        new OA\Property(property: "price", type: "number", example: 25000),
                        new OA\Property(property: "image", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Menu item master berhasil dibuat")
        ]
    )]
    public function adminCreateMenuItem() {}

    #[OA\Post(
        path: "/admin/menu-items/import",
        summary: "Impor menu item dari file CSV (Super Admin)",
        tags: ["5. Menu Item"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "file", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Impor CSV berhasil")
        ]
    )]
    public function importMenuItems() {}

    #[OA\Put(
        path: "/admin/menu-items/{menuItem}",
        summary: "Perbarui menu item master (Super Admin)",
        tags: ["5. Menu Item"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "menuItem", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                    new OA\Property(property: "name", type: "string", example: "Kopi Susu Aren Special"),
                    new OA\Property(property: "price", type: "number", example: 28000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Menu master berhasil diperbarui")
        ]
    )]
    public function adminUpdateMenuItem() {}

    #[OA\Delete(
        path: "/admin/menu-items/{menuItem}",
        summary: "Hapus menu item master (Super Admin)",
        tags: ["5. Menu Item"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "menuItem", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Menu master berhasil dihapus")
        ]
    )]
    public function adminDeleteMenuItem() {}

    // ==========================================
    // 6. Menu per Cabang & Assignment
    // ==========================================

    #[OA\Get(
        path: "/branches/{branch}/menus",
        summary: "Daftar menu & stok cabang spesifik",
        tags: ["6. Menu per Cabang"],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Daftar menu per cabang")
        ]
    )]
    public function getBranchMenus() {}

    #[OA\Get(
        path: "/branches/{branch}/menus/{menuItem}",
        summary: "Detail menu item di cabang spesifik",
        tags: ["6. Menu per Cabang"],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "menuItem", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detail menu cabang")
        ]
    )]
    public function getBranchMenuItemDetail() {}

    #[OA\Post(
        path: "/admin/branches/{branch}/menu-items",
        summary: "Assign/hubungkan menu item ke cabang (Super Admin)",
        tags: ["6. Menu per Cabang"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["menu_item_ids"],
                properties: [
                    new OA\Property(property: "menu_item_ids", type: "array", items: new OA\Items(type: "integer"), example: [1, 2, 3])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Menu berhasil di-assign ke cabang")
        ]
    )]
    public function assignMenuItemsToBranch() {}

    #[OA\Post(
        path: "/admin/branches/{branch}/copy-menus",
        summary: "Salin seluruh menu dari cabang lain (Super Admin)",
        tags: ["6. Menu per Cabang"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["source_branch_id"],
                properties: [
                    new OA\Property(property: "source_branch_id", type: "integer", example: 1),
                    new OA\Property(property: "overwrite", type: "boolean", example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Menu berhasil disalin antar cabang")
        ]
    )]
    public function copyBranchMenus() {}

    #[OA\Delete(
        path: "/admin/branches/{branch}/menu-items",
        summary: "Unassign/lepaskan menu dari cabang (Super Admin)",
        tags: ["6. Menu per Cabang"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["menu_item_ids"],
                properties: [
                    new OA\Property(property: "menu_item_ids", type: "array", items: new OA\Items(type: "integer"), example: [2])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Menu berhasil dilepas dari cabang")
        ]
    )]
    public function unassignMenuItemsFromBranch() {}

    #[OA\Get(
        path: "/admin/branches/{branch}/stock",
        summary: "Lihat stok menu cabang (Admin & Super Admin)",
        tags: ["11. Manajemen Stok Cabang"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Data stok cabang")
        ]
    )]
    public function getBranchStock() {}

    #[OA\Put(
        path: "/admin/branches/{branch}/menu-items/{menuItem}/stock",
        summary: "Update stok menu cabang (Admin & Super Admin)",
        tags: ["11. Manajemen Stok Cabang"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "branch", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "menuItem", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["stock"],
                properties: [
                    new OA\Property(property: "stock", type: "integer", example: 50),
                    new OA\Property(property: "is_available", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Stok berhasil diperbarui")
        ]
    )]
    public function updateBranchStock() {}
}
