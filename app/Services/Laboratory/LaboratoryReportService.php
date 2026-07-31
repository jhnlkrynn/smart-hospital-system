<?php

namespace App\Services\Laboratory;

use App\Models\LaboratoryResult;

class LaboratoryReportService
{
    public function pdf(LaboratoryResult $result): string
    {
        $lines = [
            'Smart Hospital Management System',
            'Laboratory Result Report',
            '',
            'Request: '.$result->laboratoryRequest->request_number,
            'Patient: '.$result->patient->full_name,
            'Test: '.$result->laboratoryTest->name,
            'Result: '.$this->displayValue($result),
            'Reference: '.($result->text_reference ?: trim(($result->reference_lower_bound ?? '').' - '.($result->reference_upper_bound ?? '').' '.$result->unit)),
            'Flag: '.$result->abnormal_flag->label(),
            'Released: '.$result->released_at?->format('M d, Y h:i A'),
            '',
            'Laboratory results must be interpreted by an authorized healthcare professional together with the patient clinical history and condition.',
            'A result outside the reference range does not always indicate a medical condition. Please consult your healthcare provider for interpretation.',
        ];

        return $this->simplePdf($lines);
    }

    private function displayValue(LaboratoryResult $result): string
    {
        $value = $result->numeric_value ?? $result->text_value ?? $result->qualitative_value ?? ($result->boolean_value === null ? null : ($result->boolean_value ? 'Detected' : 'Not detected'));

        return trim(($value ?? 'See attached result').' '.($result->unit ?? ''));
    }

    /**
     * Generate a minimal single-page PDF without external dependencies.
     *
     * @param list<string> $lines
     */
    private function simplePdf(array $lines): string
    {
        $content = "BT\n/F1 11 Tf\n50 780 Td\n";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -18 Td\n";
            }
            $content .= '('.$this->escape($line).") Tj\n";
        }
        $content .= "ET";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length ".strlen($content)." >>\nstream\n{$content}\nendstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
