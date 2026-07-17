<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_token',
        'remember_token',
        'secret',
    ];

    public function log(
        string $action,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $reason = null,
        ?Request $request = null,
        ?int $userId = null
    ): AuditLog {
        $request ??= request();

        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'reason' => $reason,
        ]);
    }

    private function sanitize(array $values): array
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            Arr::forget($values, $key);
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->sanitize($value);
            }
        }

        return $values;
    }
}
