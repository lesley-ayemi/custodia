<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::with('user')->orderByDesc('created_at')->paginate(20);

        return AuditLogResource::collection($logs);
    }
}
