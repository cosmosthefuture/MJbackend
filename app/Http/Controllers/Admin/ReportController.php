<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\Report\AgentCommissionByMonthRequest;
use App\Http\Requests\Admin\Report\DailyDepositListRequest;
use App\Http\Requests\Admin\Report\DailyHouseCutListRequest;
use App\Http\Requests\Admin\Report\DailyMoneyTransferReportListRequest;
use App\Http\Requests\Admin\Report\DailyProfitReportListRequest;
use App\Http\Requests\Admin\Report\DepositReportRequest;
use App\Http\Requests\Admin\Report\MasterCommissionByMonthRequest;
use App\Http\Requests\Admin\Report\MoneyTransferReportRequest;
use App\Http\Requests\Admin\Report\ProfitReportRequest;
use App\Http\Requests\Admin\Report\UserGameHistoryListRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Report\HouseCutReportRequest;
use App\Http\Services\Admin\ReportService;
use Illuminate\Support\Facades\Validator;

class ReportController extends ApiController
{
    private $report_service;

    public function __construct(ReportService $report_service)
    {
        $this->report_service = $report_service;
    }

    public function getHouseCutReports(HouseCutReportRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();

            $res_data = $this->report_service->getHouseCutReports($validated['type']);
            return $this->successResponse($res_data, 200, 'House Cut Reports');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getDailyHouseCutReportsWithPagination(DailyHouseCutListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $startDate = array_key_exists('start_date', $validated) ? $validated['start_date'] : null;
            $endDate = array_key_exists('end_date', $validated) ? $validated['end_date'] : null;

            $res_data = $this->report_service->getDailyHouseCutReportsWithPagination($per_page, $page, $startDate, $endDate);
            return $this->paginatedSuccessResponse($res_data, 200, 'Daily House Cut Report Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getDepositReports(DepositReportRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();

            $res_data = $this->report_service->getDepositReports($validated['type']);
            return $this->successResponse($res_data, 200, 'Deposit Reports');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getDailyDepositReportsWithPagination(DailyDepositListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $startDate = array_key_exists('start_date', $validated) ? $validated['start_date'] : null;
            $endDate = array_key_exists('end_date', $validated) ? $validated['end_date'] : null;

            $res_data = $this->report_service->getDailyDepositReportsWithPagination($per_page, $page, $startDate, $endDate);
            return $this->paginatedSuccessResponse($res_data, 200, 'Daily Deposit Report Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getMoneyTransferReports(MoneyTransferReportRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();

            $res_data = $this->report_service->getMoneyTransferReports($validated['type']);
            return $this->successResponse($res_data, 200, 'Money Transfer Reports');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getDailyMoneyTransferReportsWithPagination(DailyMoneyTransferReportListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $startDate = array_key_exists('start_date', $validated) ? $validated['start_date'] : null;
            $endDate = array_key_exists('end_date', $validated) ? $validated['end_date'] : null;

            $res_data = $this->report_service->getDailyMoneyTransferReportsWithPagination($per_page, $page, $startDate, $endDate);
            return $this->paginatedSuccessResponse($res_data, 200, 'Daily Money Transfer Report Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getProfitReports(ProfitReportRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();

            $res_data = $this->report_service->getProfitReports($validated['type']);
            return $this->successResponse($res_data, 200, 'Profit Reports');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getDailyProfitReportsWithPagination(DailyProfitReportListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $startDate = array_key_exists('start_date', $validated) ? $validated['start_date'] : null;
            $endDate = array_key_exists('end_date', $validated) ? $validated['end_date'] : null;

            $res_data = $this->report_service->getDailyProfitReportsWithPagination($per_page, $page, $startDate, $endDate);
            return $this->paginatedSuccessResponse($res_data, 200, 'Daily Profit Report Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getUserGameHistory(UserGameHistoryListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->report_service->getUserGameHistoryWithPagination($validated['user_id'], $per_page, $page);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Game History Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getMasterCommissionByMonth(MasterCommissionByMonthRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->report_service->getMasterCommissionByMonth($per_page, $page, $validated['month']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Master Commission Reports');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getAgentCommissionByMonth(AgentCommissionByMonthRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->report_service->getAgentCommissionByMonth($per_page, $page, $validated['month']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Commission Reports');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}