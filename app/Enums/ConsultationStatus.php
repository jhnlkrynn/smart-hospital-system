<?php

namespace App\Enums;

enum ConsultationStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Finalized = 'finalized';
    case Amended = 'amended';
}
