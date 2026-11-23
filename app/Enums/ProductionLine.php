<?php

namespace App\Enums;

enum ProductionLine: string
{
    case L1 = 'L1';
    case L2 = 'L2';
    case L3 = 'L3';
    case L4 = 'L4';

    public function label(): string
    {
        return match ($this) {
            self::L1 => 'ไลน์ 1',
            self::L2 => 'ไลน์ 2',
            self::L3 => 'ไลน์ 3',
            self::L4 => 'ไลน์ 4',
        };
    }
}
