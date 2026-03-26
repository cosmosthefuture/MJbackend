<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\PaymentMethodRepository;
use App\Http\Repositories\BaseRepo;
use App\Models\Admin;
use Exception;
use Str;

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

    public function create(array $attributes)
    {
        try {
            $result = $this->payment_method_repository->create($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create payment method: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $attributes)
    {
        try {
            $result = $this->payment_method_repository->update($id, $attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update payment method: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->payment_method_repository->delete($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to delete payment method: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->payment_method_repository->whereFirst($column, $value);
            if(!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find payment method with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function togglePaymentMethodStatus($data)
    {
        $this->payment_method_repository->toggleActive($data);
    }
}
