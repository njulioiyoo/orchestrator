<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;

class PermissionController extends Controller
{
    // Permission middleware is now handled in routes

    public function index()
    {
        return Inertia::render('system/permissions/Index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = Permission::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('system.permissions.edit', $row->encrypted_id);
                    return '
                        <a href="' . $editUrl . '" class="btn btn-info btn-sm" title="Edit">
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
        return Inertia::render('system/permissions/Create');
    }

    public function store(StorePermissionRequest $request)
    {
        Permission::create([
            'name' => $request->name,
            'group' => $request->group,
            'guard_name' => 'web'
        ]);

        return redirect()->route('system.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $id)
    {
        $permission = $id;
        return Inertia::render('system/permissions/Edit', [
            'permission' => $permission->toArray() + ['encrypted_id' => $permission->encrypted_id]
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $id)
    {
        $permission = $id;

        $permission->update([
            'name' => $request->name,
            'group' => $request->group
        ]);

        return redirect()->route('system.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $id)
    {
        $permission = $id;
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully.'], 200);
    }
}
