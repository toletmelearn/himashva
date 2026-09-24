<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Lightweight audit trail for admin-sensitive models (Product, Coupon,
 * Order, User). Logs create/update/delete with before/after diffs.
 * Excludes sensitive attributes (password, remember_token) from the
 * logged payload regardless of which model uses this trait.
 */
trait Auditable
{
    protected static array $auditExcluded = ['password', 'remember_token', 'updated_at', 'created_at'];

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::writeAuditLog($model, 'created', null, $model->attributesToArray());
        });

        static::updated(function ($model) {
            $changes = array_diff_key($model->getChanges(), array_flip(static::$auditExcluded));
            if (empty($changes)) {
                return;
            }

            $before = array_intersect_key($model->getOriginal(), $changes);

            static::writeAuditLog($model, 'updated', $before, $changes);
        });

        static::deleted(function ($model) {
            static::writeAuditLog($model, 'deleted', $model->attributesToArray(), null);
        });
    }

    protected static function writeAuditLog($model, string $action, ?array $before, ?array $after): void
    {
        $before = static::redact($before);
        $after = static::redact($after);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'before' => $before,
            'after' => $after,
            'ip_address' => request()?->ip(),
        ]);
    }

    protected static function redact(?array $attributes): ?array
    {
        if ($attributes === null) {
            return null;
        }

        return collect($attributes)->except(static::$auditExcluded)->all();
    }
}
