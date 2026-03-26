<?php

namespace App\Http\Requests\Admin\GameRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'game_rule_id' => [
                'required',
                'exists:game_rules,id',
            ],

            'game_id' => [
                'required',
                'exists:games,id',
            ],

            'room_name' => [
                'required',
                'string',
                'max:255',
            ],

            'room_code' => [
                'required',
                'string',
                'max:50',
                'unique:game_rooms,room_code',
            ],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        return $errors;
    }
}
