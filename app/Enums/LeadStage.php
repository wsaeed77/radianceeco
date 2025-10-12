<?php

namespace App\Enums;

enum LeadStage: string
{
    case RISHI_SUBMISSION = 'rishi_submission';
    case BOILER_TEAM = 'boiler_team';
    case LOFT_TEAM = 'loft_team';
    case RADIANCE_TEAM = 'radiance_team';
    case ANESCO = 'anesco';
    case UNKNOWN = 'unknown';

    /**
     * Get user-friendly label for display
     */
    public function label(): string
    {
        return match($this) {
            self::RISHI_SUBMISSION => 'Rishi Submission',
            self::BOILER_TEAM => 'Boiler Team',
            self::LOFT_TEAM => 'Loft Team',
            self::RADIANCE_TEAM => 'Radiance Team',
            self::ANESCO => 'Anesco',
            self::UNKNOWN => 'Unknown',
        };
    }

    public static function fromRaw(?string $stage): self
    {
        if (empty($stage)) {
            return self::UNKNOWN;
        }

        return match (strtolower(trim($stage))) {
            'rishi submission', 'rishi', 'submission' => self::RISHI_SUBMISSION,
            'boiler team', 'boiler' => self::BOILER_TEAM,
            'loft team', 'loft' => self::LOFT_TEAM,
            'radiance team', 'radiance' => self::RADIANCE_TEAM,
            'anesco' => self::ANESCO,
            default => self::UNKNOWN,
        };
    }
}