<?php

namespace App\Enums;

enum AllergyType: string
{
    case Medicine = 'medicine';
    case Food = 'food';
    case Environmental = 'environmental';
    case Other = 'other';
}
