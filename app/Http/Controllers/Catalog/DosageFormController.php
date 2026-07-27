<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Dosage_Form;
use Illuminate\Http\Request;

class DosageFormController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $dosage_form = Dosage_Form::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name_ar', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.Dosage_Form.index', compact('dosage_form', 'q'));
    }

    public function create()
    {
        return view('admin.Dosage_Form.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        Dosage_Form::create([
            'name_ar' => $request->input('name_ar'),
            'name_en' => $request->input('name_en'),
            'is_active' => $request->input('is_active'),
        ]);

        return redirect()->route('dosage_form.index')->with('success', 'تم اضافة الدواء بنجاح.');
    }

    public function edit($id)
    {
        $dosage_form = Dosage_Form::findOrFail($id);
        return view('admin.Dosage_Form.edit', compact('dosage_form'));
    }

    public function update(Request $request, $id)
    {
        $dosage_form = Dosage_Form::findOrFail($id);

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $dosage_form->update([
            'name_ar' => $request->input('name_ar'),
            'name_en' => $request->input('name_en'),
            'is_active' => $request->input('is_active'),
        ]);

        return redirect()->route('dosage_form.index')->with('success', 'تم تعديل شكل الدواء بنجاح.');
    }

    public function destroy($id)
    {
        Dosage_Form::destroy($id);
        return redirect()->route('dosage_form.index')->with('success', 'تم حذف شكل الدواء بنجاح.');
    }

    public function trash()
    {
        $dosage_form = Dosage_Form::onlyTrashed()->get();
        return view('admin.Dosage_Form.trash', compact('dosage_form'));
    }

    public function restore($id)
    {
        Dosage_Form::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('dosage_form.trash')->with('success', 'تم استعادة شكل الدواء بنجاح.');
    }

    public function forceDelete($id)
    {
        Dosage_Form::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('dosage_form.trash')->with('success', 'تم حذف شكل الدواء نهائياً.');
    }
}
