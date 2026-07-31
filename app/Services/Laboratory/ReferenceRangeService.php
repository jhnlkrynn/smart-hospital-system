<?php

namespace App\Services\Laboratory;

use App\Enums\LaboratoryAbnormalFlag;
use App\Models\LaboratoryReferenceRange;
use App\Models\LaboratoryTest;
use App\Models\Patient;

class ReferenceRangeService
{
    public function resolve(LaboratoryTest $test, Patient $patient): ?LaboratoryReferenceRange
    {
        $ageDays = $patient->date_of_birth ? (int) $patient->date_of_birth->diffInDays(now('Asia/Manila')) : null;
        $sex = $patient->sex?->value ?? $patient->sex;

        return LaboratoryReferenceRange::query()
            ->where('laboratory_test_id', $test->id)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('sex')->orWhere('sex', $sex))
            ->where(fn ($query) => $query->whereNull('minimum_age_days')->orWhere('minimum_age_days', '<=', $ageDays))
            ->where(fn ($query) => $query->whereNull('maximum_age_days')->orWhere('maximum_age_days', '>=', $ageDays))
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now('Asia/Manila')->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now('Asia/Manila')->toDateString()))
            ->orderByRaw('sex is null')
            ->orderByDesc('minimum_age_days')
            ->first();
    }

    public function flag(?float $value, ?LaboratoryReferenceRange $range): LaboratoryAbnormalFlag
    {
        if ($value === null) {
            return LaboratoryAbnormalFlag::NotApplicable;
        }

        if (! $range) {
            return LaboratoryAbnormalFlag::Indeterminate;
        }

        if ($range->critical_lower_bound !== null && $value < (float) $range->critical_lower_bound) {
            return LaboratoryAbnormalFlag::CriticalLow;
        }

        if ($range->critical_upper_bound !== null && $value > (float) $range->critical_upper_bound) {
            return LaboratoryAbnormalFlag::CriticalHigh;
        }

        if ($range->lower_bound !== null && $value < (float) $range->lower_bound) {
            return LaboratoryAbnormalFlag::Low;
        }

        if ($range->upper_bound !== null && $value > (float) $range->upper_bound) {
            return LaboratoryAbnormalFlag::High;
        }

        return LaboratoryAbnormalFlag::Normal;
    }
}
