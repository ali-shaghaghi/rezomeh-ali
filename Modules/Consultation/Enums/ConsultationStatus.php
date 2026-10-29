<?php

namespace Modules\Consultation\Enums;

enum ConsultationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Done = 'done';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار تایید',
            self::Confirmed => 'تایید شده',
            self::Done => 'انجام شده',
            self::Cancelled => 'لغو شده',
            self::NoShow => 'عدم حضور',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Confirmed => 'sky',
            self::Done => 'emerald',
            self::Cancelled => 'rose',
            self::NoShow => 'slate',
        };
    }
}