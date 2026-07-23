<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

class AuthDocs
{
    #[OA\Post(
        path: "/register",
        summary: "Registrasi customer baru",
        description: "Pendaftaran akun customer baru. Mengirimkan email verifikasi otomatis.",
        tags: ["1. Autentikasi"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Budi Santoso"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "budi@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "Secret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Registrasi berhasil",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Registrasi berhasil. Silakan cek email untuk verifikasi."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", type: "object"),
                                new OA\Property(property: "access_token", type: "string", example: "1|abc123def..."),
                                new OA\Property(property: "token_type", type: "string", example: "Bearer")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validasi gagal")
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: "/login",
        summary: "Login pengguna",
        description: "Login menggunakan email dan password untuk mendapatkan access token.",
        tags: ["1. Autentikasi"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "budi@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!"),
                    new OA\Property(property: "remember_me", type: "boolean", example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login berhasil"),
            new OA\Response(response: 401, description: "Email atau password salah")
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/forgot-password",
        summary: "Minta link reset password",
        tags: ["1. Autentikasi"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "budi@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Email reset password berhasil dikirim")
        ]
    )]
    public function forgotPassword() {}

    #[OA\Post(
        path: "/reset-password",
        summary: "Reset password dengan token",
        tags: ["1. Autentikasi"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["token", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "token", type: "string", example: "abc123token"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "budi@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "NewSecret123!"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "NewSecret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password berhasil direset")
        ]
    )]
    public function resetPassword() {}

    #[OA\Post(
        path: "/email/resend",
        summary: "Kirim ulang email verifikasi",
        tags: ["1. Autentikasi"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Email verifikasi berhasil dikirim ulang")
        ]
    )]
    public function resendVerification() {}

    #[OA\Get(
        path: "/email/verify/{id}/{hash}",
        summary: "Verifikasi alamat email dari tautan",
        tags: ["1. Autentikasi"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "hash", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Email berhasil diverifikasi")
        ]
    )]
    public function verifyEmail() {}

    #[OA\Post(
        path: "/logout",
        summary: "Logout pengguna",
        tags: ["1. Autentikasi"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logout berhasil")
        ]
    )]
    public function logout() {}

    #[OA\Get(
        path: "/user",
        summary: "Ambil data profil pengguna aktif",
        tags: ["2. Profil User"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Data profil berhasil diambil")
        ]
    )]
    public function getUserProfile() {}

    #[OA\Put(
        path: "/user/profile",
        summary: "Perbarui profil nama pengguna",
        tags: ["2. Profil User"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Budi Santoso Updated")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profil berhasil diperbarui")
        ]
    )]
    public function updateProfile() {}
}
