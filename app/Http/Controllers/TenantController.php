<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('users')
            ->latest()
            ->paginate(10);

        return Inertia::render('system/tenants/Index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = Tenant::withCount('users');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('users_count', function ($row) {
                    return $row->users_count;
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = '/system/tenants/' . $row->encrypted_id;
                    $editUrl = '/system/tenants/' . $row->encrypted_id . '/edit';
                    return '
                        <button type="button" data-id="' . $row->encrypted_id . '" class="btn btn-info btn-sm js-view" title="View">
                            <i class="fa fa-eye"></i>
                        </button>
                        <a href="' . $editUrl . '" class="btn btn-warning btn-sm" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button type="button" data-id="' . $row->encrypted_id . '" class="btn btn-danger btn-sm js-delete" title="Delete">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function create()
    {
        return Inertia::render('system/tenants/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tenants,slug',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain',
            'config' => 'nullable|array',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (Tenant::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $tenant = Tenant::create($validated);

        return redirect()->route('system.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $id)
    {
        $tenant = $id;
        $tenant->load(['users' => function($query) {
            $query->select('id', 'name', 'email', 'tenant_id');
        }]);

        return Inertia::render('system/tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    public function detail(Tenant $id, Request $request)
    {
        if ($request->ajax()) {
            $tenant = $id;
            $tenant->load(['users' => function($query) {
                $query->select('id', 'name', 'email', 'tenant_id');
            }]);

            $tenantArray = $tenant->toArray();
            $tenantArray['encrypted_id'] = $tenant->getRouteKey();

            return response()->json([
                'tenant' => $tenantArray
            ]);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function edit(Tenant $id)
    {
        $tenant = $id;
        return Inertia::render('system/tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'slug')->ignore($id->id)
            ],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain')->ignore($id->id)
            ],
            'subdomain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'subdomain')->ignore($id->id)
            ],
            'config' => 'nullable|array',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $tenant = $id;
        $tenant->update($validated);

        return redirect()->route('system.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $id, Request $request)
    {
        // Prevent deletion if tenant has users
        $tenant = $id;
        if ($tenant->users()->count() > 0) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Cannot delete tenant with existing users.'], 400);
            }
            return back()->with('error', 'Cannot delete tenant with existing users.');
        }

        $tenant->delete();

        if ($request->ajax()) {
            return response()->json(['success' => 'Tenant deleted successfully.']);
        }

        return redirect()->route('system.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    public function switch(Request $request, Tenant $tenant)
    {
        $user = $request->user();
        
        // Check if user belongs to this tenant
        if ($user->tenant_id !== $tenant->id) {
            abort(403, 'You do not have access to this tenant.');
        }

        // Store tenant in session
        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('dashboard')
            ->with('success', "Switched to tenant: {$tenant->name}");
    }

    public function config(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'config' => 'required|array',
        ]);

        $tenant->update([
            'config' => array_merge($tenant->config ?? [], $validated['config'])
        ]);

        return back()->with('success', 'Tenant configuration updated successfully.');
    }

    public function getConfig(Tenant $tenant, $key = null)
    {
        if ($key) {
            return response()->json([
                'value' => $tenant->getConfigValue($key)
            ]);
        }

        return response()->json([
            'config' => $tenant->config
        ]);
    }
}