<?php

namespace App\Http\Requests\Master\Withdraw;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequestCreateRequest extends FormRequest
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
            'payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'password' => [
                'required'
            ],

            'receiver_phone_number' => [
                'required',
                'regex:/^09\d{8,9}$/'
            ]
        ];
    }

    public function messages()
    {
        return [
            'receiver_phone_number.regex' =>
                'Phone number must start with 09 and contain 8 or 9 digits after it.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        return $errors;
    }
}
