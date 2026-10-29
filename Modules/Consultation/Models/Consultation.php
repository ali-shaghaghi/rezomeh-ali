<?php

namespace Modules\Consultation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Consultation\Enums\ConsultationMode;
use Modules\Consultation\Enums\ConsultationStatus;

class Consultation extends Model
{
    protected $fillable = [
        'user_id',
        'consultant_id',
        'type_id',
        'slot_id',
        'order_id',
        'mode',
        'status',
        'meeting_link',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ConsultationType::class, 'type_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ConsultationSlot::class, 'slot_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\Modules\Orders\Models\Order::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ConsultationNote::class);
    }

    /**
     * وضعیت به‌صورت Enum
     */
    public function statusEnum(): ConsultationStatus
    {
        return ConsultationStatus::from($this->status);
    }

    public function modeEnum(): ConsultationMode
    {
        return ConsultationMode::from($this->mode);
    }
}