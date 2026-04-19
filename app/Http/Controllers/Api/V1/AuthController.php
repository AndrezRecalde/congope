<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Iniciar Sesión
     * 
     * @unauthenticated
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->respondUnauthorized('Credenciales incorrectas');
        }

        if (!$user->activo) {
            return $this->respondUnauthorized('Su cuenta está desactivada. Por favor contacte al administrador.');
        }

        $user->tokens()->delete();
        $token = $user->createToken('congope_token');

        return $this->respondSuccess([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'requires_password_change' => $user->requires_password_change,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'provincias' => $user->provincias()->pluck('provincias.nombre', 'provincias.id'),
            ]
        ], 'Inicio de sesión exitoso');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->respondSuccess(null, 'Sesión cerrada');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->respondSuccess([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'requires_password_change' => $user->requires_password_change,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'provincias' => $user->provincias()->pluck('provincias.nombre', 'provincias.id'),
        ], 'Usuario autenticado obtenido correctamente');
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('congope_token');

        return $this->respondSuccess([
            'token' => $token->plainTextToken
        ], 'Token refrescado exitosamente');
    }
}
