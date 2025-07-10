<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // Permission middleware is now handled in routes

    public function index()
    {
        return Inertia::render('system/users/Index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with('roles');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('roles', function ($row) {
                    return $row->roles->pluck('name')->join(', ');
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('system.users.edit', $row->encrypted_id);
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
        $roles = Role::all();
        return Inertia::render('system/users/FormPage', [
            'roles' => $roles
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Assign role
        if ($request->role) {
            $user->assignRole($request->role);
        }

        return redirect()->route('system.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $id)
    {
        $user = $id->load('roles');
        $roles = Role::all();

        return Inertia::render('system/users/FormPage', [
            'user' => $user->toArray() + ['encrypted_id' => $user->encrypted_id],
            'roles' => $roles,
            'userRoles' => $user->roles->pluck('name')->toArray()
        ]);
    }

    public function update(UpdateUserRequest $request, User $id)
    {
        $user = $id;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
        ]);

        // Sync role
        if ($request->role) {
            $user->syncRoles([$request->role]);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('system.users.index')
            ->with('success', 'User updated successfully.')
            ->with('user_updated', true);
    }

    public function destroy(User $id)
    {
        $user = $id;

        // Prevent deletion of current user
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
