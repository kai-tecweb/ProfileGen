<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProposalRequest extends FormRequest
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
            'x_profile' => ['required', 'string'],
            'instagram_profile' => ['required', 'string'],
            'coconala_profile' => ['required', 'string'],
            'product_design' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'x_profile.required' => 'X用プロフィールは必須です。',
            'instagram_profile.required' => 'Instagram用プロフィールは必須です。',
            'coconala_profile.required' => 'ココナラ用プロフィールは必須です。',
            'product_design.required' => '商品設計案は必須です。',
        ];
    }
}
