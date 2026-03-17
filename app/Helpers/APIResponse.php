<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class APIResponse
{
    //this function use to frequently return succeess response
    public static function success(
        mixed $data,
        int $code = 200,
        string $message = 'Success',
        ?array $meta = null
    ): JsonResponse {
        $response = [
            'success' => true,
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ];

        if($meta !== null){
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    //this function use to frequently return error response
    public static function error(
        mixed $errors = null,
        int $code = 500,
        string $message = 'Error',
        ?string $errorCode = null,
        ?array $meta = null
    ): JsonResponse {
        if (is_string($errors) && ($message === 'Error' || trim($message) === '')) {
            $message = $errors;
            $errors = null;
        }

        $response = [
            'success' => false,
            'code' => $code,
            'message' => $message
        ];

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        if($errors !== null){
            $response['errors'] = $errors;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }
    public static function formatPagination(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array

    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
