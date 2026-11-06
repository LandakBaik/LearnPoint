<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return response JSON sukses terstandarisasi.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse(mixed $data = null, string $message = 'Sukses', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return response JSON gagal / error terstandarisasi.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse(string $message = 'Terjadi kesalahan.', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Response data berhasil dibuat (HTTP 201 Created).
     */
    public function createdResponse(mixed $data = null, string $message = 'Data berhasil dibuat.'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Response validasi gagal (HTTP 422 Unprocessable Entity).
     */
    public function validationErrorResponse(mixed $errors, string $message = 'Validasi gagal.'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Response tidak terautentikasi (HTTP 401 Unauthorized).
     */
    public function unauthorizedResponse(string $message = 'Akses tidak diizinkan. Silakan login kembali.'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Response hak akses ditolak (HTTP 403 Forbidden).
     */
    public function forbiddenResponse(string $message = 'Anda tidak memiliki hak akses untuk tindakan ini.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Response data tidak ditemukan (HTTP 404 Not Found).
     */
    public function notFoundResponse(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }
}

