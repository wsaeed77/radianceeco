<?php

namespace App\Enums;

enum LeadSource: string
{
    case ONLINE = 'Online';
    case DOOR_KNOCKING = 'Door knocking';
    case REFERENCE_CLIENT = 'Reference (Client)';
    case REFERENCE_OTHER = 'Reference (Other)';
    case IMPORT = 'Import';
    case UNKNOWN = 'Unknown';

    /**
     * Get user-friendly label for display
     */
    public function label(): string
    {
        return $this->value;
    }

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

    public static function fromRaw(?string $source): self
    {
        if (empty($source)) {
            return self::UNKNOWN;
        }

        return match (strtolower(trim($source))) {
            'online', 'web', 'website' => self::ONLINE,
            'door knocking', 'door', 'knocking' => self::DOOR_KNOCKING,
            'reference (client)', 'client reference', 'client' => self::REFERENCE_CLIENT,
            'reference (other)', 'other reference', 'reference' => self::REFERENCE_OTHER,
            'import', 'imported', 'bulk import' => self::IMPORT,
            default => self::UNKNOWN,
        };
    }
}