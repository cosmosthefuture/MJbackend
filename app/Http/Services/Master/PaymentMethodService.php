<?php

namespace App\Http\Services\Master;

use App\Http\Repositories\Master\PaymentMethodRepository;
use Exception;

class PaymentMethodService
{
    protected $payment_method_repository;

    public function __construct(PaymentMethodRepository $payment_method_repository)
    {
        $this->payment_method_repository = $payment_method_repository;
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
        ?string $status = null)
    {
        try {
            $result = $this->payment_method_repository->getDataWithPagination(page: $page, perPage: $perPage, status: $status, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch payment method data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->payment_method_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch payment method: ' . $e->getMessage());
            throw $e;
        }
    }
}
