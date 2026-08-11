<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function __construct(private AuditLogService $auditLogService) {}

    /**
     * Display a listing of audit logs (Superadmin and Operator).
     */
    public function index(Request $request)
    {
        return Inertia::render('Audit/Index', $this->auditLogService->getIndexData($request, Auth::user()));
    }
}
