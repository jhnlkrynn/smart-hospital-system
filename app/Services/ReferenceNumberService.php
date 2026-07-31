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

    public function appointmentNumber(): string
    {
        return $this->generate('APT', now('Asia/Manila')->format('Y'), 6);
    }

    public function queueNumber(string $departmentCode): string
    {
        $date = now('Asia/Manila')->format('Ymd');

        return $this->generate(strtoupper($departmentCode), $date, 3);
    }

    public function consultationNumber(): string
    {
        return $this->generate('CON', now('Asia/Manila')->format('Y'), 6);
    }

    public function medicalCertificateNumber(): string
    {
        return $this->generate('MEDCERT', now('Asia/Manila')->format('Y'), 6);
    }

    public function laboratoryRequestNumber(): string
    {
        return $this->generate('LAB', now('Asia/Manila')->format('Ymd'), 4);
    }

    public function laboratoryAccessionNumber(): string
    {
        return $this->generate('ACC', now('Asia/Manila')->format('Ymd'), 5);
    }

    public function medicationNumber(): string
    {
        return $this->generate('MED', now('Asia/Manila')->format('Y'), 6);
    }

    public function prescriptionNumber(): string
    {
        return $this->generate('RX', now('Asia/Manila')->format('Ymd'), 5);
    }

    public function pharmacySupplierNumber(): string
    {
        return $this->generate('SUP', now('Asia/Manila')->format('Y'), 6);
    }

    public function pharmacyPurchaseNumber(): string
    {
        return $this->generate('PO', now('Asia/Manila')->format('Ymd'), 5);
    }

    public function pharmacyTransferNumber(): string
    {
        return $this->generate('TRF', now('Asia/Manila')->format('Ymd'), 5);
    }

    public function stockCountNumber(): string
    {
        return $this->generate('SC', now('Asia/Manila')->format('Ymd'), 5);
    }

    public function inventoryTransactionNumber(): string
    {
        return $this->generate('INVTXN', now('Asia/Manila')->format('Ymd'), 6);
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
