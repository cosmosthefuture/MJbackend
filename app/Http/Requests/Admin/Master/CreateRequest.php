<?php

namespace App\Http\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            // 'email' => [
            //     'required',
            //     'email',
            //     'max:255',
            //     'unique:masters,email',
            // ],

            'phone_number' => [
                'required',
                'string',
                'max:20',
                'unique:masters,phone_number',
            ],

            'username' => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/^[a-z0-9]+$/',
                'unique:masters,username',
            ],

            'winning_commission_percentage' => [
                'required',
                'integer',
                'min:1',
                'max:100'
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
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
