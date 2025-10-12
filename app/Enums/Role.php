<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case AGENT = 'agent';
    case READONLY = 'readonly';
}