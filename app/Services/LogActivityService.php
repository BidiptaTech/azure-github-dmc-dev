<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogActivityService
{
    public static function log($action, $tableName, $recordId = null, $changes = [])
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'changes' => $changes,
        ]);
    }
}
