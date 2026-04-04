<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data, $message, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function successPaginated($paginator, $message, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total()
            ]
        ], $code);
    }

    public static function error($message, $errors = [], $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    public static function notFound($message = "Recurso no encontrado")
    {
        return self::error($message, [], 404);
    }

    public static function forbidden($message = "No tienes permiso para esta acción")
    {
        return self::error($message, [], 403);
    }

    public static function serverError($message = "Error interno del servidor")
    {
        return self::error($message, [], 500);
    }

    public static function unauthorized($message = 'No autenticado')
    {
        return self::error($message, [], 401);
    }
}
