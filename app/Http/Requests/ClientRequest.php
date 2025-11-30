<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string'],
            'questionnaire_text' => ['nullable', 'string'],
        ];

        // 編集時はanswers_textを必須にする
        if ($this->route()->getName() === 'clients.update') {
            $rules['answers_text'] = ['required', 'string'];
        } else {
            $rules['answers_text'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '名前は必須です。',
            'name.max' => '名前は255文字以内で入力してください。',
            'company.max' => '会社名は255文字以内で入力してください。',
            'answers_text.required' => 'ヒアリング回答は必須です。',
        ];
    }
}
