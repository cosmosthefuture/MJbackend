<?php

namespace App\Http\Requests\Master\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $id = $this->route('id');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email,' . $id,
            ],

            'phone_number' => [
                'required',
                'string',
                'max:20',
                'unique:users,phone_number,' . $id,
            ],

            'username' => [
                'nullable',
                'string',
                'min:4',
                'max:30',
                'regex:/^[a-z0-9]+$/',
                'unique:users,username,' . $id,
            ],

            'agent_code' => [
                'required',
                'string',
                'exists:agents,agent_code'
            ],

            // 'password' => [
            //     'required',
            //     'string',
            //     'min:8',
            //     'confirmed',
            // ],
            // 'otp' => ['required', 'digits:6']
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
