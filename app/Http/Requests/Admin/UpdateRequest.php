<?php

namespace App\Http\Requests\Admin;

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

    public function rules(): array
    {
        $id = $this->route('id');
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
            //     'unique:admins,email,' . $id,
            // ],

            'phone_number' => [
                'required',
                'string',
                'max:20',
                'unique:admins,phone_number,' . $id,
            ],

            'username' => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/^[a-z0-9]+$/',
                'unique:admins,username,' . $id,
            ],

            'permission_type_ids' => ['array', 'required', 'min:1'],
            'permission_type_ids.*' => ['exists:permission_types,id'],
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
