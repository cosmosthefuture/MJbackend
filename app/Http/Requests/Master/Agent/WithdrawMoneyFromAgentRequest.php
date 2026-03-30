<?php

namespace App\Http\Requests\Master\Agent;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawMoneyFromAgentRequest extends FormRequest
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
            'agent_id' => [
                'required',
                'integer',
                'exists:agents,id',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' =>
                'Username must contain only lowercase letters and numbers, with no spaces or special characters.',
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        return $errors;
    }
}
