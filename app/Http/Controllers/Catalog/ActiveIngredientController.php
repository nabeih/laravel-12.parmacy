<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Active_Ingredient;
use Illuminate\Http\Request;

class ActiveIngredientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $ingredients = Active_Ingredient::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name_ar', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->get();

        return view('Admin.active_Ingredient.index', compact('ingredients', 'q'));
    }

    public function create()
    {
        return view('admin.Active_Ingredient.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:100',
            'name_ar' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);
        Active_Ingredient::create([
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
            'is_active' => $request->input('is_active')
        ]);
        return redirect()->route('activeingredient.index')->with('success', 'تم اضافة مادة فعالة بنجاح.');
    }

    public function edit($id)
    {
        $ingredient = Active_Ingredient::findOrFail($id);
        return view('admin.Active_Ingredient.edit', compact('ingredient'));
    }

    public function update(Request $request, $id)
    {
        $ingredient = Active_Ingredient::findOrFail($id);

        $request->validate([
            'name_en' => 'required|string|max:100',
            'name_ar' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $ingredient->update([
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
            'is_active' => $request->input('is_active'),
        ]);

        return redirect()->route('activeingredient.index')->with('success', 'تم تعديل المادة الفعالة بنجاح.');
    }

    public function destroy($id)
    {
        Active_Ingredient::destroy($id);
        return redirect()->route('activeingredient.index')->with('success', 'تم حذف المادة الفعالة بنجاح.');
    }

    public function trash()
    {
        $ingredients = Active_Ingredient::onlyTrashed()->get();
        return view('admin.Active_Ingredient.trash', compact('ingredients'));
    }

    public function restore($id)
    {
        Active_Ingredient::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('activeingredient.trash')->with('success', 'تم استعادة المادة الفعالة بنجاح.');
    }

    public function forceDelete($id)
    {
        Active_Ingredient::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('activeingredient.trash')->with('success', 'تم حذف المادة الفعالة نهائياً.');
    }
}
