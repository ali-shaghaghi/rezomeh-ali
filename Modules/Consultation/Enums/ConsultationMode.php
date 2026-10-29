<?php

namespace Modules\Consultation\Enums;

enum ConsultationMode: string
{
    case Online = 'online';
    case Phone = 'phone';
    case InPerson = 'in_person';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'آنلاین',
            self::Phone => 'تلفنی',
            self::InPerson => 'حضوری',
        };
    }
}