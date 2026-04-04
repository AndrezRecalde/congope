<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        //'two_factor_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            //'two_factor_enabled' => 'boolean',
        ];
    }

    public function provincias()
    {
        return $this->belongsToMany(Provincia::class, 'usuario_provincia')
                    ->using(UsuarioProvincia::class);
    }

    public function proyectosCreados()
    {
        return $this->hasMany(Proyecto::class, 'creado_por');
    }

    public function documentosSubidos()
    {
        return $this->hasMany(Documento::class, 'subido_por');
    }

    public function valoraciones()
    {
        return $this->hasMany(ValoracionPractica::class);
    }

    public function esDeProvinciaId(string $provinciaId): bool
    {
        return $this->provincias()->where('provincias.id', $provinciaId)->exists();
    }

    public function tieneProvincia(): bool
    {
        return $this->provincias()->exists();
    }

    public function esSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
