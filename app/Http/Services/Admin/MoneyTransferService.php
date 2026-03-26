<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\MoneyTransferRepository;

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
            $result = $this->money_transfer_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch user money transfer records with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->money_transfer_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch user money transfer record: ' . $e->getMessage());
            throw $e;
        }
    }
}
