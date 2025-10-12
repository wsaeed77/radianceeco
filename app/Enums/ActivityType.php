<?php

namespace App\Enums;

enum ActivityType: string
{
    case NOTE = 'note';
    case STATUS_CHANGE = 'status_change';
    case STAGE_CHANGE = 'stage_change';
    case FILE_UPLOAD = 'file_upload';
    case VISIT_BOOKED = 'visit_booked';
    case DOCUMENT_REQUESTED = 'document_requested';
    case CALLED_CLIENT = 'called_client';
    case PROPERTY_VISITED = 'property_visited';
    case SURVEY = 'survey';
    case INSTALLATION = 'installation';
    case DATA_MATCH_SENT = 'data_match_sent';
    case PRE_APPROVAL_SENT = 'pre_approval_sent';
    case SUBMITTED = 'submitted';

    /**
     * Get user-selectable activity types (exclude automatic types)
     */
    public static function userSelectable(): array
    {
        return [
            self::NOTE,
            self::VISIT_BOOKED,
            self::DOCUMENT_REQUESTED,
            self::CALLED_CLIENT,
            self::PROPERTY_VISITED,
            self::SURVEY,
            self::INSTALLATION,
            self::DATA_MATCH_SENT,
            self::PRE_APPROVAL_SENT,
            self::SUBMITTED,
        ];
    }

    /**
     * Check if this type is automatic (system-generated)
     */
    public function isAutomatic(): bool
    {
        return in_array($this, [
            self::STATUS_CHANGE,
            self::STAGE_CHANGE,
            self::FILE_UPLOAD,
        ]);
    }

    /**
     * Get user-friendly label for display
     */
    public function label(): string
    {
        return match($this) {
            self::NOTE => 'Note',
            self::STATUS_CHANGE => 'Status Change',
            self::STAGE_CHANGE => 'Stage Change',
            self::FILE_UPLOAD => 'File Upload',
            self::VISIT_BOOKED => 'Visit Booked',
            self::DOCUMENT_REQUESTED => 'Document Requested',
            self::CALLED_CLIENT => 'Called Client',
            self::PROPERTY_VISITED => 'Property Visited',
            self::SURVEY => 'Survey',
            self::INSTALLATION => 'Installation',
            self::DATA_MATCH_SENT => 'Data Match Sent',
            self::PRE_APPROVAL_SENT => 'Pre Approval Sent',
            self::SUBMITTED => 'Submitted',
        };
    }
}