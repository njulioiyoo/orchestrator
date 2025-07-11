<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;

class MenuController extends Controller
{
    public function index()
    {
        try {
            // Get only root menus with their nested children structure
            $menus = Menu::with(['children' => function($query) {
                $query->with(['children' => function($subQuery) {
                    $subQuery->orderBy('sort_order');
                }])->orderBy('sort_order');
            }])
            ->whereNull('parent_id') 
            ->orderBy('sort_order')
            ->get();
            
        } catch (\Exception $e) {
            // If table doesn't exist yet, return empty array
            $menus = collect([]);
        }

        return Inertia::render('system/menus/Index', [
            'menus' => $menus
        ]);
    }

    public function create()
    {
        $parentMenus = Menu::whereNull('parent_id')
            ->ordered()
            ->get();

        return Inertia::render('system/menus/Create', [
            'parentMenus' => $parentMenus
        ]);
    }

    public function store(StoreMenuRequest $request)
    {
        Menu::create($request->validated());

        return redirect()->route('system.menus.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $id)
    {
        $parentMenus = Menu::whereNull('parent_id')
            ->where('id', '!=', $id->id)
            ->ordered()
            ->get();

        return Inertia::render('system/menus/Edit', [
            'menu' => $id,
            'parentMenus' => $parentMenus
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $id)
    {
        $id->update($request->validated());

        return redirect()->route('system.menus.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $id)
    {
        if ($id->children()->count() > 0) {
            return back()->withErrors(['error' => 'Menu tidak dapat dihapus karena memiliki sub-menu.']);
        }

        $id->delete();

        return redirect()->route('system.menus.index')
            ->with('success', 'Menu berhasil dihapus.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:menus,id',
            'menus.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->menus as $menuData) {
            Menu::where('id', $menuData['id'])
                ->update(['sort_order' => $menuData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function getMenusJson()
    {
        try {
            $menus = Menu::with(['children' => function($query) {
                $query->active()->ordered()->with(['children' => function($subQuery) {
                    $subQuery->active()->ordered();
                }]);
            }])
            ->active()
            ->rootMenus()
            ->ordered()
            ->get();

            return response()->json($menus);
        } catch (\Exception $e) {
            // If table doesn't exist yet, return empty array
            return response()->json([]);
        }
    }
}
