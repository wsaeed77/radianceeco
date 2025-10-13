<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case HOLD = 'hold';
    case NOT_POSSIBLE = 'not_possible';
    case NEED_TO_VISIT_PROPERTY = 'need_to_visit_property';
    case PROPERTY_VISITED = 'property_visited';
    case SURVEY_BOOKED = 'survey_booked';
    case SURVEY_DONE = 'survey_done';
    case DATA_UPDATED_IN_GOOGLE_DRIVE = 'data_updated_in_google_drive';
    case NEED_TO_SEND_DATA_MATCH = 'need_to_send_data_match';
    case DATA_MATCH_SENT = 'data_match_sent';
    case NEED_TO_BOOK_INSTALLATION = 'need_to_book_installation';
    case INSTALLATION_BOOKED = 'installation_booked';
    case PROPERTY_INSTALLED = 'property_installed';
    case UNKNOWN = 'unknown';

    /**
     * Get user-friendly label for display
     */
    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::HOLD => 'Hold',
            self::NOT_POSSIBLE => 'Not Possible',
            self::NEED_TO_VISIT_PROPERTY => 'Need To Visit Property',
            self::PROPERTY_VISITED => 'Property Visited',
            self::SURVEY_BOOKED => 'Survey Booked',
            self::SURVEY_DONE => 'Survey Done',
            self::DATA_UPDATED_IN_GOOGLE_DRIVE => 'Data Updated In Google Drive',
            self::NEED_TO_SEND_DATA_MATCH => 'Need To Send Data Match',
            self::DATA_MATCH_SENT => 'Data Match Sent',
            self::NEED_TO_BOOK_INSTALLATION => 'Need To Book Installation',
            self::INSTALLATION_BOOKED => 'Installation Booked',
            self::PROPERTY_INSTALLED => 'Property Installed',
            self::UNKNOWN => 'Unknown',
        };
    }

    public static function fromRaw(?string $status): self
    {
        if (empty($status)) {
            return self::UNKNOWN;
        }

        return match (strtolower(trim($status))) {
            'new' => self::NEW,
            'hold', 'on hold' => self::HOLD,
            'not possible', 'impossible', 'cannot proceed' => self::NOT_POSSIBLE,
            'need to visit property', 'need visit', 'visit required' => self::NEED_TO_VISIT_PROPERTY,
            'property visited', 'visited' => self::PROPERTY_VISITED,
            'survey booked', 'booked survey' => self::SURVEY_BOOKED,
            'survey done', 'survey completed' => self::SURVEY_DONE,
            'data updated in google drive', 'google drive updated' => self::DATA_UPDATED_IN_GOOGLE_DRIVE,
            'need to send data match', 'data match needed' => self::NEED_TO_SEND_DATA_MATCH,
            'data match sent', 'sent data match' => self::DATA_MATCH_SENT,
            'need to book installation', 'installation booking needed' => self::NEED_TO_BOOK_INSTALLATION,
            'installation booked', 'booked installation' => self::INSTALLATION_BOOKED,
            'property installed', 'installed' => self::PROPERTY_INSTALLED,
            default => self::UNKNOWN,
        };
    }
}