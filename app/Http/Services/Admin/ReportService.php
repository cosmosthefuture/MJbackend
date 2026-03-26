<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\ReportRepository;
use Exception;

class ReportService
{
    protected $report_repository;

    public function __construct(ReportRepository $report_repository)
    {
        $this->report_repository = $report_repository;
    }

    public function getHouseCutReports($type)
    {
        if ($type == "daily") {
            return $this->report_repository->getDailyHouseCutReports();
        } elseif ($type == "monthly") {
            return $this->report_repository->getMonthlyHouseCutReports();
        } elseif ($type == "quarterly") {
            return $this->report_repository->getQuarterlyHouseCutReports();
        }
    }

    public function getDailyHouseCutReportsWithPagination($per_page, $page, $startDate, $endDate)
    {
        return $this->report_repository->getDailyHouseCutReportsWithPagination($per_page, $page, $startDate, $endDate);
    }

    public function getDepositReports($type)
    {
        if ($type == "daily") {
            return $this->report_repository->getDailyDepositReports();
        } elseif ($type == "monthly") {
            return $this->report_repository->getMonthlyDepositReports();
        } elseif ($type == "quarterly") {
            return $this->report_repository->getQuarterlyDepositReports();
        }
    }

    public function getDailyDepositReportsWithPagination($per_page, $page, $startDate, $endDate)
    {
        return $this->report_repository->getDailyDepositReportsWithPagination($per_page, $page, $startDate, $endDate);
    }

    public function getMoneyTransferReports($type)
    {
        if ($type == "daily") {
            return $this->report_repository->getDailyMoneyTransferReports();
        } elseif ($type == "monthly") {
            return $this->report_repository->getMonthlyMoneyTransferReports();
        } elseif ($type == "quarterly") {
            return $this->report_repository->getQuarterlyMoneyTransferReports();
        }
    }

    public function getDailyMoneyTransferReportsWithPagination($per_page, $page, $startDate, $endDate)
    {
        return $this->report_repository->getDailyMoneyTransferReportsWithPagination($per_page, $page, $startDate, $endDate);
    }

    public function getProfitReports($type)
    {
        if ($type == "daily") {
            return $this->report_repository->getDailyProfitReports();
        } elseif ($type == "monthly") {
            return $this->report_repository->getMonthlyProfitReports();
        } elseif ($type == "quarterly") {
            return $this->report_repository->getQuarterlyProfitReports();
        }
    }

    public function getDailyProfitReportsWithPagination($per_page, $page, $startDate, $endDate)
    {
        return $this->report_repository->getDailyProfitReportsWithPagination($per_page, $page, $startDate, $endDate);
    }

    public function getUserGameHistoryWithPagination($uesrId, $per_page, $page)
    {
        return $this->report_repository->getUserGameHistoryWithPagination($uesrId, $per_page, $page);
    }

    public function getMasterCommissionByMonth($per_page, $page, $month)
    {
        return $this->report_repository->getMasterCommissionByMonth($per_page, $page, $month);
    }

    public function getAgentCommissionByMonth($per_page, $page, $month)
    {
        return $this->report_repository->getAgentCommissionByMonth($per_page, $page, $month);
    }
}
