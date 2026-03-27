<?php

namespace App\Http\Services\Master;

use App\Http\Repositories\Master\MasterRepository;
use DB;
use Exception;

class MasterService
{
    protected $master_repository;

    public function __construct(MasterRepository $master_repository)
    {
        $this->master_repository = $master_repository;
    }

    public function generateAccessToken($master)
    {
        try {
            $result = $this->master_repository->generateAccessToken($master);
            $master->update(['last_logined' => now()]);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to generate master access token: ' . $e->getMessage());
            throw $e;
        }
    }

    public function logout()
    {
        try {
            $currentAccessToken = auth()->user()->currentAccessToken();

            $currentAccessToken->delete();

            auth()->user()->tokens()->where(
                'id',
                $currentAccessToken->id
            )->delete();
            return true;
        } catch (Exception $e) {
            logger()->error('Error : Failed to master logout: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->master_repository->whereFirst($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find master with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDailyWalletSummary($page = 1, $per_page = 10, $masterId): array
    {
        try {
            $result = $this->master_repository->getDailyWalletSummary($page, $per_page, $masterId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch master daily wallet summary: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getMonthlyIncentiveReport($page = 1, $per_page = 1, $masterId)
    {
        DB::beginTransaction();
        try {
            $result = $this->master_repository->getMonthlyIncentiveReport($page, $per_page, $masterId);

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to fetch master monthly incentive report: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getMasterWalletRecords($page = 1, $per_page = 1, $masterId)
    {
        DB::beginTransaction();
        try {
            $result = $this->master_repository->getMasterWalletRecords($page, $per_page, $masterId);

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to fetch master wallet record: ' . $e->getMessage());
            throw $e;
        }
    }
}
