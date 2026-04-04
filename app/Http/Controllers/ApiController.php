<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    protected function respondSuccess($data, $message, $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    protected function respondPaginated($paginator, $message)
    {
        return ApiResponse::successPaginated($paginator, $message);
    }

    protected function respondError($message, $errors = [], $code = 422)
    {
        return ApiResponse::error($message, $errors, $code);
    }

    protected function respondNotFound($message = "Recurso no encontrado")
    {
        return ApiResponse::notFound($message);
    }

    protected function respondForbidden($message = "Sin permiso")
    {
        return ApiResponse::forbidden($message);
    }

    protected function respondCreated($data, $message = "Creado exitosamente")
    {
        return ApiResponse::success($data, $message, 201);
    }

    protected function respondUnauthorized($message = 'No autenticado')
    {
        return ApiResponse::unauthorized($message);
    }

    protected function respondServerError($message = 'Error interno del servidor')
    {
        return ApiResponse::serverError($message);
    }
}
