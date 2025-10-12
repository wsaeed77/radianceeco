<?php

namespace App\Enums;

enum LeadSource: string
{
    case ONLINE = 'Online';
    case DOOR_KNOCKING = 'Door knocking';
    case REFERENCE_CLIENT = 'Reference (Client)';
    case REFERENCE_OTHER = 'Reference (Other)';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->value;
        }
        return $options;
    }
}