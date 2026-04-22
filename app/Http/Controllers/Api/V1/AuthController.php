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
            'telefono' => $user->telefono,
            'cargo' => $user->cargo,
            'entidad' => $user->entidad,
            'dni' => $user->dni,
            'activo' => $user->activo,
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

    /**
     * PUT /api/v1/auth/perfil
     *
     * Actualiza el nombre y/o email del usuario
     * autenticado. Cualquier usuario puede usar
     * este endpoint — no requiere permisos especiales.
     *
     * No permite cambiar el rol ni las provincias.
     * Para eso están los endpoints de admin.
     */
    public function actualizarPerfil(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($usuario->id),
            ],
            'telefono' => ['sometimes', 'nullable', 'string', 'max:20'],
            'cargo' => ['sometimes', 'nullable', 'string', 'max:150'],
            'entidad' => ['sometimes', 'nullable', 'string', 'max:200'],
            'dni' => ['sometimes', 'nullable', 'string', 'max:20'],
        ], [
            'name.max' => 'El nombre no puede superar 200 caracteres.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado en el sistema.',
            'telefono.max' => 'El teléfono no puede superar 20 caracteres.',
            'cargo.max' => 'El cargo no puede superar 150 caracteres.',
            'entidad.max' => 'La entidad no puede superar 200 caracteres.',
            'dni.max' => 'El DNI no puede superar 20 caracteres.',
        ]);

        // Verificar que se envió al menos un campo
        if (empty($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes enviar al menos un campo para actualizar.',
                'errors' => [],
            ], 422);
        }

        $emailOriginal = $usuario->email;

        $usuario->update($validated);

        // Si el email cambió, marcar como no verificado
        if (isset($validated['email']) && $validated['email'] !== $emailOriginal) {
            $usuario->email_verified_at = null;
            $usuario->save();
        }

        // Recargar el usuario actualizado
        $usuario->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'data' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'cargo' => $usuario->cargo,
                'entidad' => $usuario->entidad,
                'dni' => $usuario->dni,
                'roles' => $usuario->getRoleNames()->toArray(),
                'email_verified_at' => $usuario->email_verified_at,
            ],
        ]);
    }

    /**
     * PUT /api/v1/auth/password
     *
     * Cambia la contraseña del usuario autenticado.
     * Requiere la contraseña actual para confirmar
     * la identidad — seguridad básica.
     *
     * No requiere permisos especiales — cualquier
     * usuario autenticado puede cambiar su propia
     * contraseña.
     */
    public function cambiarPassword(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $request->validate([
            'password_actual' => [
                'required',
                'string',
            ],
            'password_nuevo' => [
                'required',
                'string',
                'min:8',
                'confirmed', // requiere password_nuevo_confirmation
            ],
        ], [
            'password_actual.required' => 'Debes ingresar tu contraseña actual.',
            'password_nuevo.required' => 'Debes ingresar la nueva contraseña.',
            'password_nuevo.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password_nuevo.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->password_actual, $usuario->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta.',
                'errors' => [
                    'password_actual' => ['La contraseña actual es incorrecta.'],
                ],
            ], 422);
        }

        // Verificar que la nueva contraseña sea diferente a la actual
        if (Hash::check($request->password_nuevo, $usuario->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La nueva contraseña debe ser diferente a la actual.',
                'errors' => [
                    'password_nuevo' => ['La nueva contraseña debe ser diferente a la actual.'],
                ],
            ], 422);
        }

        // Actualizar la contraseña
        $usuario->update([
            'password' => Hash::make($request->password_nuevo),
        ]);

        // Registrar en auditoría/log
        \Log::info(
            'Contraseña cambiada por: ' . $usuario->email .
            ' desde IP: ' . $request->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Contraseña cambiada correctamente. Por seguridad, cierra sesión en otros dispositivos.',
        ]);
    }
}
