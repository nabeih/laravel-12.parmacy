<?php

namespace App\Http\Controllers\Portal;

use App\Exceptions\AiServiceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\ChatRequest;
use App\Http\Requests\Portal\ScanPrescriptionRequest;
use App\Services\GeminiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AiController extends Controller
{
    public function __construct(private readonly GeminiClient $ai) {}

    public function scannerPage(): View
    {
        return view('User.scanner');
    }

    public function assistantPage(): View
    {
        return view('User.assistant');
    }

    public function chat(ChatRequest $request): JsonResponse
    {
        $messages = collect($request->validated('messages'))
            ->map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']])
            ->all();

        try {
            $reply = $this->ai->chat($messages);
        } catch (AiServiceException $e) {
            return response()->json(['message' => 'تعذر الاتصال بالمساعد الذكي حالياً.'], 502);
        }

        return response()->json(['reply' => $reply]);
    }

    public function scanPrescription(ScanPrescriptionRequest $request): JsonResponse
    {
        try {
            $result = $this->ai->extractPrescription(
                $request->validated('image'),
                $request->validated('mimeType'),
            );
        } catch (AiServiceException $e) {
            return response()->json(['message' => 'تعذر تحليل صورة الروشتة حالياً.'], 502);
        }

        return response()->json(['medicines' => $result['medicines'], 'source' => 'gemini']);
    }
}
