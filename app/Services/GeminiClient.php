<?php

namespace App\Services;

use App\Exceptions\AiServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiClient
{
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models';

    private const CHAT_SYSTEM_PROMPT = 'أنت فارم، مساعد صيدلاني ذكي. ساعد المستخدمين في الاستفسار عن الأدوية والجرعات والتفاعلات الدوائية. تحدث بالعربية. لا تشخّص أمراضاً. انصح دائماً بمراجعة الصيدلاني للحالات الجدية.';

    private const EXTRACTION_SYSTEM_PROMPT = <<<'PROMPT'
أنت مساعد يستخرج بيانات الأدوية من صورة روشتة طبية (عربية أو إنجليزية).
أعد النتيجة بصيغة JSON فقط بالشكل التالي، بدون أي نص إضافي:
{"medicines": [{"name": "اسم الدواء", "dosage": "الجرعة", "frequency": "التكرار", "duration": "المدة", "notes": "ملاحظات"}]}
إذا كان أي حقل غير واضح في الصورة، اجعل قيمته سلسلة نصية فارغة "". إذا لم تجد أي دواء، أعد {"medicines": []}.
PROMPT;

    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $model = null,
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages): string
    {
        $payload = [
            'system_instruction' => ['parts' => [['text' => self::CHAT_SYSTEM_PROMPT]]],
            'contents' => array_map(fn ($m) => [
                'role' => $m['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m['content']]],
            ], $messages),
            // maxOutputTokens covers both the model's internal "thinking" tokens and the
            // visible reply on this model family — too low and the answer gets cut off
            // mid-sentence once thinking eats the budget (observed: ~480 thinking tokens
            // for a simple question), even though finishReason still reports MAX_TOKENS.
            'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 2048],
        ];

        $text = $this->extractText($this->request($payload));

        if ($text === null || $text === '') {
            throw new AiServiceException('Gemini chat response was empty.');
        }

        return $text;
    }

    /**
     * @return array{medicines: array<int, array{name: string, dosage: string, frequency: string, duration: string, notes: string}>}
     */
    public function extractPrescription(string $base64Image, string $mimeType): array
    {
        $payload = [
            'system_instruction' => ['parts' => [['text' => self::EXTRACTION_SYSTEM_PROMPT]]],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => 'استخرج الأدوية من صورة الروشتة التالية.'],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 2048,
                'responseMimeType' => 'application/json',
            ],
        ];

        $text = $this->extractText($this->request($payload));

        if ($text === null || $text === '') {
            throw new AiServiceException('Gemini vision response was empty.');
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded) || ! isset($decoded['medicines']) || ! is_array($decoded['medicines'])) {
            throw new AiServiceException('Gemini vision response was not valid JSON.');
        }

        return $decoded;
    }

    private function extractText(array $data): ?string
    {
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function request(array $payload): array
    {
        $key = $this->apiKey ?? config('services.gemini.key');

        if (! $key) {
            throw new AiServiceException('GEMINI_API_KEY is not configured.');
        }

        $model = $this->model ?? config('services.gemini.model', 'gemini-flash-latest');

        try {
            $response = Http::timeout(30)
                ->post(self::API_BASE.'/'.$model.':generateContent?key='.$key, $payload);
        } catch (Throwable $e) {
            Log::error('Gemini request failed.', ['error' => $e->getMessage()]);
            throw new AiServiceException('Failed to reach Gemini.', previous: $e);
        }

        if ($response->failed()) {
            Log::error('Gemini returned an error response.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new AiServiceException('Gemini returned an error response.');
        }

        return $response->json();
    }
}
