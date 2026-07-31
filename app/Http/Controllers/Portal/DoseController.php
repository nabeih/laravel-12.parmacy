<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreDoseRequest;
use App\Http\Requests\Portal\UpdateDoseRequest;
use App\Models\Dose;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoseController extends Controller
{
    public function page(): View
    {
        return view('User.doses');
    }

    public function index(Request $request): JsonResponse
    {
        $doses = $request->user()->doses()->orderByDesc('active')->orderBy('name_ar')->get();

        return response()->json(['doses' => $doses]);
    }

    public function store(StoreDoseRequest $request): JsonResponse
    {
        $dose = $request->user()->doses()->create([
            ...$request->validated(),
            'active' => true,
        ]);

        return response()->json(['dose' => $dose], 201);
    }

    public function update(UpdateDoseRequest $request, Dose $dose): JsonResponse
    {
        if ($dose->user_id !== $request->user()->id) {
            abort(404);
        }

        $dose->update($request->validated());

        return response()->json(['dose' => $dose]);
    }

    public function destroy(Request $request, Dose $dose): JsonResponse
    {
        if ($dose->user_id !== $request->user()->id) {
            abort(404);
        }

        $dose->delete();

        return response()->json(['message' => 'تم حذف الدواء.']);
    }
}
