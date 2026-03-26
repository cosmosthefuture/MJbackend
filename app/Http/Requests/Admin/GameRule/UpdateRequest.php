<?php

namespace App\Http\Requests\Admin\GameRule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'rule_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('game_rules')
                    ->where(fn($q) => $q->where('game_id', $this->game_id))
                    ->ignore($id),
            ],

            'min_bet_amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'max_bet_amount' => [
                'required',
                'numeric',
                'gte:min_bet_amount',
            ],

            'time_per_round' => [
                'required',
                'integer',
                'min:1',
            ],

            'user_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'game_id' => [
                'required',
                'exists:games,id',
            ],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        return $errors;
    }
}
