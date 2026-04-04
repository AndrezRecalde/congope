<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UsuarioProvincia extends Pivot
{
    use HasUuids;

    protected $table = 'usuario_provincia';
}
