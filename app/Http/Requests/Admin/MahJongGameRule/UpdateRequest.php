<?php

namespace App\Http\Requests\Admin\MahJongGameRule;

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
                Rule::unique('mah_jong_game_rules')
                    ->ignore($id),
            ],
            'match_qty_per_round' => ['required', 'integer', 'min:1'],
            'max_player' => ['required', 'integer', 'min:2'],
            'bet_amount' => ['required', 'numeric', 'min:0'],

            'fees' => ['required', 'array', 'size:3'],

            'fees.*.fee_type' => [
                'required',
                'in:room,registration,winning_commission',
                'distinct'
            ],
            'fees.*.amount' => ['required', 'numeric', 'min:0'],
            'fees.*.payer_type' => [
                'required',
                'in:winner,each_player'
            ],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        return $errors;
    }
}
