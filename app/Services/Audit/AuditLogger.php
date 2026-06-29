<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public function log(string $action, ?User $user = null, ?string $description = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
