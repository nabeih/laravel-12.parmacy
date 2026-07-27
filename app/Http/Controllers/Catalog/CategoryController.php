<?php

namespace App\Http\Controllers\Catalog;


use App\Models\Medicine_Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $categories = Medicine_Category::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name_ar', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%");
                });
            })
            ->get();

        return view('admin.categories.catalog', compact('categories', 'q'));
    }

    public function create()
    {
        return view('admin.categories.createcatalog');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        Medicine_Category::create([
            'name_ar' => $request->input('name_ar'),
            'name_en' => $request->input('name_en'),
            'is_active' => $request->input('is_active'),
        ]);


        return redirect()->route('category.index')->with('success', 'Category created successfully.');
    }
    public function destroy($id)
    {
        Medicine_Category::destroy($id);
        return redirect()->route('category.index')->with('success', 'Category deleted successfully.');}

    public function edit($id)
    {
        $category = Medicine_Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Medicine_Category::findOrFail($id);

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $category->update([
            'name_ar' => $request->input('name_ar'),
            'name_en' => $request->input('name_en'),
            'is_active' => $request->input('is_active'),
        ]);

        return redirect()->route('category.index')->with('success', 'Category updated successfully.');
    }

    public function trash()
    {
        $categories = Medicine_Category::onlyTrashed()->get();
        return view('admin.categories.trash', compact('categories'));
    }

    public function restore($id)
    {
        Medicine_Category::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('category.trash')->with('success', 'تم استعادة التصنيف بنجاح.');
    }

    public function forceDelete($id)
    {
        Medicine_Category::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('category.trash')->with('success', 'تم حذف التصنيف نهائياً.');
    }
}
