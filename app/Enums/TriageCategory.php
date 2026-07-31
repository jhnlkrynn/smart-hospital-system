<?php

namespace App\Enums;

enum TriageCategory: string
{
    case Emergency = 'emergency';
    case Urgent = 'urgent';
    case Priority = 'priority';
    case Standard = 'standard';
}
