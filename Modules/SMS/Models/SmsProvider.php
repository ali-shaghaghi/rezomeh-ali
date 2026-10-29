<?php

namespace Modules\SMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'driver',
        'api_key',
        'sender',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
}