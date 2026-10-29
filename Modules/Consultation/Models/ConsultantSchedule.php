<?php

namespace Modules\Consultation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantSchedule extends Model
{
    protected $fillable = [
        'consultant_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    /**
     * لیبل روز هفته — 0=شنبه ... 6=جمعه
     */
    public function dayLabel(): string
    {
        return self::dayLabelFor($this->day_of_week);
    }

    public static function dayLabelFor(int $day): string
    {
        return ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'][$day] ?? '—';
    }
}