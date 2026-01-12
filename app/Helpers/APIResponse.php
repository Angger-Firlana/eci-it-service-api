<?php

use App\Helpers;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;

class APIResponse
{
    public function success(
        mixed $data,
        int $code = 200,
        string $message = 'Success',
        ?array $meta = null
    ):JSONResponse{
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];

        if($meta !== null){
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    public function error(
        mixed $errors = null,
        int $code = 500,
        string $message = 'Error',
    ){
        $response = [
            'success' => false,
            'message' => $message
        ];

        if($errors !== null){
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}