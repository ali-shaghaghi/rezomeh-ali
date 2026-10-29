<?php

namespace Modules\Consultation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConsultationSlot extends Model
{
    protected $fillable = [
        'consultant_id',
        'date',
        'start_time',
        'end_time',
        'is_booked',
        'booked_at',
    ];

    protected $casts = [
        'date' => 'date',
        'is_booked' => 'boolean',
        'booked_at' => 'datetime',
    ];

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class, 'slot_id');
    }
}