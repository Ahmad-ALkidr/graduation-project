<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
             // حقل 'content' مطلوب ويجب أن يكون نصاً
            'content' => 'required|string|max:10000',
            // يمكنك إضافة قواعد لملفات أخرى هنا مستقبلاً
            'image_path' => 'nullable|image|mimes:jpg,png|max:2048',
        ];
    }
}
