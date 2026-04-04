<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('registros_auditoria')]
#[Fillable('user_id', 'accion', 'modelo_tipo', 'modelo_id', 'valores_anteriores', 'valores_nuevos', 'ip_address', 'user_agent')]
class RegistroAuditoria extends BaseModel
{
    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
        ];
    }
    public $timestamps = false;
    protected $dates = ['created_at'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
