<?php

namespace App\Http\Requests\User\Deposit;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequestCreateRequest extends FormRequest
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

            'last_six_digits_of_payment_slip' => [
                'required',
                'digits:6',
            ],

            'payment_slip_image' => [
                'required',
                // 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        return $errors;
    }
}
