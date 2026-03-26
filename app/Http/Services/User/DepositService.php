<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\DepositRepository;

use App\Jobs\SendFcmToMultipleReceiversJob;
use App\Models\UserDepositRequest;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Str;

class DepositService
{
    protected $deposit_repository;

    public function __construct(DepositRepository $deposit_repository)
    {
        $this->deposit_repository = $deposit_repository;
    }

    public function getDataWithPagination(
        int $perPage = 10,
        int $page = 1,
        string $orderBy = 'created_at',
        array $searches = null,
        array $conditions = [],
        array $orConditions = [],
        array $with = [],
        ?array $whereHas = null,
        ?string $status = null
    ) {
        try {
            $result = $this->deposit_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, conditions: $conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch deposit data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getManualDeposits(
        int $perPage = 10,
        int $page = 1,
        string $orderBy = 'created_at',
        $searches = null,
        array $conditions = [],
        array $orConditions = [],
        array $with = [],
        ?array $whereHas = null,
        ?string $status = null
    ) {
        try {
            $result = $this->deposit_repository->getManualDeposits(page: $page, per_page: $perPage, with: $with);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch manual user deposit lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createDepositRequest(array $attributes)
    {
        try {
            $imagePath = null;
            if (isset($attributes['payment_slip_image']) && $attributes['payment_slip_image']->isValid()) {
                $imagePath = $attributes['payment_slip_image']->storeAs(
                    'Payment_Slip',
                    uniqid() . '.' . $attributes['payment_slip_image']->extension(),
                    'public'
                );
            }
            $imageUrl = Storage::disk('public')->url($imagePath);

            $data = [
                'user_id' => auth()->user()->id,
                'payment_method_id' => $attributes['payment_method_id'],
                'amount' => $attributes['amount'],
                'last_six_digits_of_payment_slip' => $attributes['last_six_digits_of_payment_slip'],
                'status' => 'pending',
                'payment_slip_image_url' => $imageUrl
            ];

            $result = $this->deposit_repository->create($data);
            // $origin_data = UserDepositRequest::find($result->id);

            // $adminIds = $this->deposit_repository->get_admins_for_noti($origin_data);
            // $title = 'Deposit Request';
            // $body = $origin_data->user->name . ' requested a new deposit.';

            // SendFcmToMultipleReceiversJob::dispatch($adminIds, $title, $body);

            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create user deposit request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereLatest($column, $value)
    {
        try {
            $result = $this->deposit_repository->whereLatest($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user deposit req with whereLatest: ' . $e->getMessage());
            throw $e;
        }
    }
}
