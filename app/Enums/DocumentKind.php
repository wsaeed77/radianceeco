<?php

namespace App\Enums;

enum DocumentKind: string
{
    case SURVEY_PICS = 'survey_pics';
    case FLOOR_PLAN = 'floor_plan';
    case BENEFIT_PROOF = 'benefit_proof';
    case GAS_METER = 'gas_meter';
    case EPR_REPORT = 'epr_report';
    case EPC = 'epc';
    case OTHER = 'other';
}