<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // بما أننا نستخدم middleware المصادقة على الـ route,
        // يمكننا الافتراض أن المستخدم مسجل دخوله.
        // لذا، نعيد true للسماح بالمرور إلى مرحلة التحقق من البيانات.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // هنا نضع قواعد التحقق من البيانات
        return [
            'content' => 'required|string|min:1|max:4000',
        ];
    }
}
