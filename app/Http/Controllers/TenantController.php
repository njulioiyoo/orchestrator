<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('users')
            ->latest()
            ->paginate(10);

        return Inertia::render('Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create()
    {
        return Inertia::render('Tenants/Create');
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

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['users' => function($query) {
            $query->select('id', 'name', 'email', 'tenant_id');
        }]);

        return Inertia::render('Tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    public function edit(Tenant $tenant)
    {
        return Inertia::render('Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'slug')->ignore($tenant->id)
            ],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain')->ignore($tenant->id)
            ],
            'subdomain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'subdomain')->ignore($tenant->id)
            ],
            'config' => 'nullable|array',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $tenant->update($validated);

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        // Prevent deletion if tenant has users
        if ($tenant->users()->count() > 0) {
            return back()->with('error', 'Cannot delete tenant with existing users.');
        }

        $tenant->delete();

        return redirect()->route('tenants.index')
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