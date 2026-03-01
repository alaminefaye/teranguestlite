<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        $user = Auth::user();
        $enterpriseId = $user?->enterprise_id;

        return ActivityLog::create([
            'enterprise_id' => $enterpriseId,
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'request_method' => Request::method(),
        ]);
    }
}
