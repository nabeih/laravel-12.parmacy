<?php

// namespace App\Http\Controllers\Admin;
namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;

use App\Models\Manufacturer;
use Illuminate\Http\Request;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $manufacturers = Manufacturer::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name_ar', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%");
                });
            })
            ->get();

        return view('admin.Manufacturer.index', compact('manufacturers', 'q'));
    }

    public function create()
    {
        return view('admin.Manufacturer.create');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|integer|in:0,1',
            // Add other fields as necessary
        ]);

        // Create a new manufacturer record in the database
        Manufacturer::create($validatedData);


        // Redirect to the manufacturer index page with a success message
        return redirect()->route('admin.manufacturer')->with('success', 'Manufacturer created successfully.');
    }

    public function delete($id)
    {
        Manufacturer::destroy($id);
        return redirect()->route('admin.manufacturer')->with('danger', 'تم حذف العنصر بنجاح');
    }

    public function edit($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        return view('admin.Manufacturer.edit', compact('manufacturer'));
    }

    public function update(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        $validatedData = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|integer|in:0,1',
        ]);

        $manufacturer->update($validatedData);

        return redirect()->route('admin.manufacturer')->with('success', 'تم تعديل الشركة بنجاح.');
    }

    public function trash()
    {
        $manufacturers = Manufacturer::onlyTrashed()->get();
        return view('admin.Manufacturer.trash', compact('manufacturers'));
    }

    public function restore($id)
    {
        Manufacturer::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.manufacturer.trash')->with('success', 'تم استعادة الشركة بنجاح.');
    }

    public function forceDelete($id)
    {
        Manufacturer::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('admin.manufacturer.trash')->with('success', 'تم حذف الشركة نهائياً.');
    }
}
