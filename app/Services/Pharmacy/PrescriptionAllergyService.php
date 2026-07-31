<?php

namespace App\Services\Pharmacy;

use App\Enums\AllergySeverity;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionAllergyWarning;
use App\Models\User;

class PrescriptionAllergyService
{
    public function refresh(Prescription $prescription): void
    {
        $prescription->loadMissing(['patient.allergies', 'items.medication.aliases']);
        $prescription->allergyWarnings()->delete();

        foreach ($prescription->items as $item) {
            foreach ($this->matches($prescription->patient, $item->medication) as $match) {
                PrescriptionAllergyWarning::create([
                    'prescription_id' => $prescription->id,
                    'prescription_item_id' => $item->id,
                    'patient_allergy_id' => $match['allergy']->id,
                    'medication_id' => $item->medication_id,
                    'warning_type' => 'recorded_allergy_match',
                    'severity' => $match['severity'],
                    'message' => $match['message'],
                    'requires_acknowledgment' => $this->requiresAcknowledgment($match['severity']),
                ]);
            }
        }
    }

    public function acknowledge(PrescriptionAllergyWarning $warning, User $actor, ?string $overrideReason): void
    {
        $warning->forceFill([
            'acknowledged_at' => now(),
            'acknowledged_by' => $actor->id,
            'override_reason' => $overrideReason,
        ])->save();
    }

    public function hasBlockingWarnings(Prescription $prescription): bool
    {
        return $prescription->allergyWarnings()
            ->where('requires_acknowledgment', true)
            ->whereNull('acknowledged_at')
            ->exists();
    }

    private function matches(Patient $patient, Medication $medication): array
    {
        $terms = collect([
            $medication->generic_name,
            $medication->brand_name,
            $medication->display_name,
        ])->merge($medication->aliases->pluck('alias_name'))
            ->merge($medication->allergyGroups()->pluck('name'))
            ->filter()
            ->map(fn (string $term): string => str($term)->lower()->squish()->toString())
            ->unique()
            ->values();

        return $patient->allergies
            ->where('is_active', true)
            ->filter(function ($allergy) use ($terms): bool {
                $allergen = str((string) $allergy->allergen)->lower()->squish()->toString();

                return $terms->contains(fn (string $term): bool => $term !== '' && ($term === $allergen || str_contains($term, $allergen) || str_contains($allergen, $term)));
            })
            ->map(function ($allergy) use ($medication): array {
                $severity = $allergy->severity instanceof AllergySeverity ? $allergy->severity->value : (string) $allergy->severity;

                return [
                    'allergy' => $allergy,
                    'severity' => $severity,
                    'message' => "Patient has an active {$severity} allergy record for {$allergy->allergen}. Review before finalizing {$medication->display_name}.",
                ];
            })
            ->values()
            ->all();
    }

    private function requiresAcknowledgment(?string $severity): bool
    {
        return in_array($severity, [AllergySeverity::Moderate->value, AllergySeverity::Severe->value], true);
    }
}
