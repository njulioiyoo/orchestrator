<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Permission;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    // Permission middleware is now handled in routes

    public function index()
    {
        return Inertia::render('system/roles/Index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::withCount('users');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('users_count', function ($row) {
                    return $row->users_count;
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('system.roles.edit', $row->id);
                    return '
                        <a href="' . $editUrl . '" class="btn btn-info btn-sm" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button type="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm js-delete" title="Delete">
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
        $permissions = Permission::all()->groupBy('group');
        return Inertia::render('system/roles/Create', [
            'permissions' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('system.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy('group');

        return Inertia::render('system/roles/Create', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $role->permissions->pluck('name')->toArray()
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('system.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deletion of important roles
        if (in_array($role->name, ['Super Admin', 'Admin'])) {
            return response()->json(['message' => 'Cannot delete system role.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.'], 200);
    }
}
