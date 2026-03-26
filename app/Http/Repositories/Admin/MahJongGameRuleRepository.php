<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\MahJongGameRule;
use App\Models\Game;
use DB;

class MahJongGameRuleRepository extends BaseRepo
{
    public function __construct(MahJongGameRule $model)
    {
        parent::__construct($model);
    }

    public function create(array $attributes)
    {
        return DB::transaction(function () use ($attributes) {

            $game = Game::where('name', 'Mah Jong')->first();
            $attributes['game_id'] = $game->id;
            $fees = $attributes['fees'] ?? [];
            unset($attributes['fees']);

            $rule = $this->model->create($attributes);

            $feeData = collect($fees)->map(function ($fee) use ($rule) {
                return [
                    'mah_jong_game_rule_id' => $rule->id,
                    'fee_type' => $fee['fee_type'],
                    'amount' => $fee['amount'],
                    'payer_type' => $fee['payer_type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            $rule->fees()->insert($feeData);

            return $rule->load('fees');
        });
    }

    public function update($id, array $attributes)
    {
        return DB::transaction(function () use ($id, $attributes) {

            $rule = $this->model->findOrFail($id);

            $fees = $attributes['fees'] ?? null;
            unset($attributes['fees']);

            $rule->update($attributes);

            if (!is_null($fees)) {

                $rule->fees()->delete();

                $feeData = collect($fees)->map(function ($fee) use ($rule) {
                    return [
                        'mah_jong_game_rule_id' => $rule->id,
                        'fee_type' => $fee['fee_type'],
                        'amount' => $fee['amount'],
                        'payer_type' => $fee['payer_type'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                $rule->fees()->insert($feeData);
            }

            return $rule->load('fees');
        });
    }

    public function find($id)
    {
        $data = MahJongGameRule::with(['game', 'createdBy', 'updatedBy', 'fees'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function toggleActive($data)
    {
        $admin_id = auth('api-admin')->user()->id;
        if ($data->status == 'active') {
            $data->update(['status' => 'inactive', 'updated_by' => $admin_id]);
        } else {
            $data->update(['status' => 'active', 'updated_by' => $admin_id]);
        }
    }
}
