<?php

namespace App\Services;

use App\Models\ReferenceSequence;
use Illuminate\Support\Facades\DB;

class ReferenceNumberService
{
    public function employeeNumber(): string
    {
        return $this->generate('EMP', now('Asia/Manila')->format('Y'), 6);
    }

    public function patientNumber(): string
    {
        return $this->generate('PAT', now('Asia/Manila')->format('Y'), 6);
    }

    public function generate(string $prefix, string $period, int $padding = 6): string
    {
        return DB::transaction(function () use ($prefix, $period, $padding): string {
            $key = "{$prefix}-{$period}";

            $sequence = ReferenceSequence::query()->where('key', $key)->lockForUpdate()->first();

            if (! $sequence) {
                $sequence = ReferenceSequence::create(['key' => $key, 'last_number' => 0]);
                $sequence = ReferenceSequence::query()->whereKey($sequence->id)->lockForUpdate()->firstOrFail();
            }

            $sequence->increment('last_number');

            return sprintf('%s-%s-%s', $prefix, $period, str_pad((string) $sequence->last_number, $padding, '0', STR_PAD_LEFT));
        });
    }
}
