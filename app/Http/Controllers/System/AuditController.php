<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;

class AuditController extends Controller
{
    public function index()
    {
        return Inertia::render('system/audits/Index');
    }

    public function data(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Audit::with('user')
                    ->orderBy('created_at', 'desc');

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('user_name', function ($row) {
                        return $row->user ? $row->user->name : 'System';
                    })
                    ->addColumn('auditable_type_formatted', function ($row) {
                        return class_basename($row->auditable_type);
                    })
                    ->addColumn('event_formatted', function ($row) {
                        return ucfirst($row->event);
                    })
                    ->addColumn('ip_address', function ($row) {
                        return $row->ip_address ?: 'N/A';
                    })
                    ->addColumn('user_agent_formatted', function ($row) {
                        return $this->formatUserAgent($row->user_agent);
                    })
                    ->addColumn('created_at_formatted', function ($row) {
                        return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : 'N/A';
                    })
                    ->addColumn('action', function ($row) {
                        return '
                            <button type="button" data-id="' . $row->id . '" class="btn btn-info btn-sm js-view" title="View Details">
                                <i class="fa fa-eye"></i>
                            </button>
                        ';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return response()->json(['message' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            \Log::error('Audit data error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load audit data: ' . $e->getMessage()], 500);
        }
    }

    public function show(Audit $audit)
    {
        $audit->load('user');
        
        return response()->json([
            'audit' => [
                'id' => $audit->id,
                'event' => ucfirst($audit->event),
                'auditable_type' => class_basename($audit->auditable_type),
                'auditable_id' => $audit->auditable_id,
                'user_name' => $audit->user ? $audit->user->name : 'System',
                'ip_address' => $audit->ip_address,
                'user_agent' => $this->formatUserAgent($audit->user_agent),
                'url' => $audit->url,
                'created_at' => $audit->created_at->format('Y-m-d H:i:s'),
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
            ]
        ]);
    }

    private function formatUserAgent($userAgent)
    {
        if (!$userAgent) return 'Unknown';
        
        // Simple user agent parsing
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        }
        
        return 'Other';
    }
}
