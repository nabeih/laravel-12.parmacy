<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreDoseRequest;
use App\Http\Requests\Portal\UpdateDoseRequest;
use App\Models\Dose;
use Illuminate\Http\Request;

class DoseController extends Controller
{
    /**
     * جرعات المستخدم النشطة.
     */
    public function index(Request $request)
    {
        $doses = $request->user()->doses()->orderByDesc('active')->orderBy('name_ar')->get();

        return response()->json([
            'status' => true,
            'data' => $doses,
        ]);
    }

    /**
     * إضافة جرعة جديدة.
     */
    public function store(StoreDoseRequest $request)
    {
        $dose = $request->user()->doses()->create([
            ...$request->validated(),
            'active' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة الجرعة بنجاح.',
            'data' => $dose,
        ], 201);
    }

    /**
     * تعديل جرعة.
     */
    public function update(UpdateDoseRequest $request, Dose $dose)
    {
        if ($dose->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'غير موجود.'], 404);
        }

        $dose->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث الجرعة بنجاح.',
            'data' => $dose,
        ]);
    }

    /**
     * حذف جرعة.
     */
    public function destroy(Request $request, Dose $dose)
    {
        if ($dose->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'غير موجود.'], 404);
        }

        $dose->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الدواء.',
        ]);
    }
}