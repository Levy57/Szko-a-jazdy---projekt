<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;

enum Status: string
{
    case completed = 'completed';
    case progress = 'progress';
    case planed = 'planed';

    public function getLabel(TranslatorInterface $translator): string
    {
        return match ($this) {
            self::completed => $translator->trans('status.completed'),
            self::progress => $translator->trans('status.progress'),
            self::planed => $translator->trans('status.planed'),
        };
    }

    public static function getArray(TranslatorInterface $translator): array
    {
        return array_combine(
            array_map(fn ($enum) => $enum->getLabel($translator), Status::cases()),
            Status::cases()
        );
    }
}
