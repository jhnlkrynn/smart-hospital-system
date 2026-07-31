<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    public function record(
        User $actor,
        string $action,
        string $module,
        ?Model $auditable = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'module' => $module,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'old_values' => $this->scrub($oldValues),
            'new_values' => $this->scrub($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * @param array<string, mixed>|null $values
     * @return array<string, mixed>|null
     */
    private function scrub(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return collect($values)
            ->reject(fn (mixed $value, string $key): bool => str_contains($key, 'password') || str_contains($key, 'token'))
            ->all();
    }
}
