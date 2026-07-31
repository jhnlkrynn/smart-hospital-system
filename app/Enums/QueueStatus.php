<?php

namespace App\Enums;

enum QueueStatus: string
{
    case Waiting = 'waiting';
    case Called = 'called';
    case Serving = 'serving';
    case Skipped = 'skipped';
    case Transferred = 'transferred';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
