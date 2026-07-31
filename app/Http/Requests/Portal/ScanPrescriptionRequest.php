<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class ScanPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'string', 'max:20000000'],
            'mimeType' => ['required', 'string', 'in:image/jpeg,image/png,image/webp,image/gif'],
        ];
    }
}
