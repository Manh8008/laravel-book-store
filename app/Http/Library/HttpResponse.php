<?php

namespace App\Http\Library;

use Symfony\Component\HttpFoundation\Response;

class HttpResponse
{
    public static function respondWithSuccess($data, $message = "", $code = 200)
    {
        return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $data,
        ], $code);
    }

    public static function respondError($message = "", $code = 401)
    {
        return response()->json([
        'success' => false,
        'errors' => $message,
        ], $code);
    }

    public static function respondUnAuthenticated($message = "Unauthenticated - Không Tìm thấy Sai")
    {
        return response()->json([
        'success' => false,
        'message' => $message
        ], Response::HTTP_UNAUTHORIZED);
    }

    public static function respondNotFound($message = "Not found")
    {
        return response()->json([
        'success' => false,
        'message' => $message
        ], Response::HTTP_NOT_FOUND);
    }
}
