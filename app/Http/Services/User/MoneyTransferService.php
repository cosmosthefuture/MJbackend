<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\MoneyTransferRepository;

use DB;
use Exception;
use Illuminate\Support\Facades\Storage;
use Str;

class MoneyTransferService
{
    protected $money_transfer_repository;

    public function __construct(MoneyTransferRepository $money_transfer_repository)
    {
        $this->money_transfer_repository = $money_transfer_repository;
    }

    public function createMoneyTransfer(array $attributes)
    {
        DB::beginTransaction();
        try {
            $result = $this->money_transfer_repository->createMoneyTransfer($attributes);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to create user money transfer record: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereLatest($column, $value)
    {
        try {
            $result = $this->money_transfer_repository->whereLatest($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user deposit req with whereLatest: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find($id)
    {
        try {
            $result = $this->money_transfer_repository->find($id);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user money transfer record: ' . $e->getMessage());
            throw $e;
        }
    }


    public function findUserByPhoneNumber($phone_number)
    {
        try {
            $result = $this->money_transfer_repository->findUserByPhoneNumber($phone_number);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user with phone number: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findUser($id)
    {
        try {
            $result = $this->money_transfer_repository->findUser($id);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function accept($record, $noti_id)
    {
        DB::beginTransaction();
        try {
            $this->money_transfer_repository->accept($record, $noti_id);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to accept user money transfer: ' . $e->getMessage());
            throw $e;
        }
    }

    public function reject($record, $noti_id)
    {
        DB::beginTransaction();
        try {
            $this->money_transfer_repository->reject($record, $noti_id);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject user money transfer: ' . $e->getMessage());
            throw $e;
        }
    }
}
