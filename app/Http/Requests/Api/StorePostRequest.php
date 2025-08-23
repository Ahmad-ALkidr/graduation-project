<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // اسمح لأي مستخدم مسجل بإنشاء منشور
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'content' مطلوب فقط إذا لم يتم إرسال 'image'
            'content' => 'required_without:image|nullable|string|max:10000',
            // 'image' مطلوب فقط إذا لم يتم إرسال 'content'
            'image' => 'required_without:content|nullable|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // إرجاع رد JSON بدلاً من إعادة التوجيه
        throw new HttpResponseException(response()->json([
            'message'   => 'The given data was invalid.',
            'errors'    => $validator->errors(),
        ], 422));
    }
}
