<?php

namespace App\Traits;

trait ApiResponses
{
    public function successResponse($data = [], $message = null, $code = 200)
    {
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function deleteSuccessResponse($message = null, $code = 200)
    {
        return response()->json([
            'status' => 1,
            'message' => $message,
        ], $code);
    }

    public function successResponseWithToken($data = [], $token = null, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 1,
            'data' => $data,
            'token' => $token,
            'message' => $message,
        ], $code);
    }

    public function errorResponse($message = null, $code = 404)
    {
        return response()->json([
            'status' => 0,
            'message' => $message,
        ], $code);
    }
}
