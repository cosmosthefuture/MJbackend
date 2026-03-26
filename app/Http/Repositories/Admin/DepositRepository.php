<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;

use App\Jobs\SendFcmToSingleReceiverJob;
use App\Models\AgentIncentive;
use App\Models\AgentIncentiveMonthlySummary;
use App\Models\AgentWalletDailySummary;
use App\Models\AgentWalletLedger;
use App\Models\DailyAgentDepositCommissionReport;
use App\Models\DailyDepositReport;
use App\Models\DailyMasterDepositCommissionReport;
use App\Models\ManualUserDepositRecord;
use App\Models\MasterIncentive;
use App\Models\MasterWalletDailySummary;
use App\Models\MasterWalletLedger;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserDepositRequest;
use Exception;
use Illuminate\Support\Facades\DB;

class DepositRepository extends BaseRepo
{
    public function __construct(UserDepositRequest $model)
    {
        parent::__construct($model);
    }

    public function getManualDepositLists($page, $per_page, $with, $searches = null)
    {
        $query = ManualUserDepositRecord::with($with)
            ->orderByDesc('created_at');

        if (!empty($searches)) {
            $query->whereHas('user', function ($q) use ($searches) {
                $q->where('name', 'LIKE', "%{$searches}%")
                    ->orWhere('phone_number', 'LIKE', "%{$searches}%");
            });
        }

        $totalCount = $query->count();

        $offset = ($page - 1) * $per_page;

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get();

        $totalPages = (int) ceil($totalCount / $per_page);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function findDepositRequest($id)
    {
        $data = UserDepositRequest::with(['user', 'actionBy', 'paymentMethod'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function whereLatest($column, $value)
    {
        $data = UserDepositRequest::where($column, $value)->latest()->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function approveDepositRequest($data)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'approved',
                'action_by' => auth()->user()->id,
            ]);
            $data->user->deposit($data->amount);
            // $agent = $data->user->agent;
            // $master = $data->user->agent->master;
            // $month = now()->format('Y-m');

            // // agent incentive section
            // $agent_incentive_amount = $data->amount * ($agent->incentive_percentage / 100);
            // $agent->deposit($agent_incentive_amount);
            // AgentIncentive::create([
            //     'date_time' => now(),
            //     'month' => $month,
            //     'user_id' => $data->user->id,
            //     'agent_id' => $agent->id,
            //     'agent_code' => $data->user->agent_code,
            //     'deposit_amount' => $data->amount,
            //     'incentive_percentage' => $agent->incentive_percentage,
            //     'incentive_amount' => $agent_incentive_amount,
            // ]);
            // $lastBalance = AgentWalletLedger::where('agent_id', $agent->id)
            //     ->latest('id')
            //     ->value('balance') ?? 0;

            // AgentWalletLedger::create([
            //     'agent_id' => $agent->id,
            //     'date' => now()->toDateString(),
            //     'amount' => $agent_incentive_amount,
            //     'type' => 'in',
            //     'balance' => $lastBalance + $agent_incentive_amount,
            //     'source_type' => 'user_deposit_request',
            //     'source_id' => $data->id,
            // ]);
            // $this->updateDailySummary(
            //     $agent->id,
            //     'in',
            //     $agent_incentive_amount,
            //     $lastBalance + $agent_incentive_amount
            // );
            // $this->updateMonthlySummary(
            //     $agent->id,
            //     $data->amount,
            //     $agent_incentive_amount
            // );

            // // master incentive section
            // $master_incentive = $master->incentive_percentage - $agent->incentive_percentage;
            // $master_incentive_amount = $data->amount * ($master_incentive / 100);
            // $master->deposit($master_incentive_amount);

            // MasterIncentive::create([
            //     'date_time' => now(),
            //     'month' => $month,
            //     'user_id' => $data->user->id,
            //     'agent_id' => $agent->id,
            //     'master_id' => $master->id,
            //     'deposit_amount' => $data->amount,
            //     'incentive_percentage' => $master_incentive,
            //     'incentive_amount' => $master_incentive_amount,
            // ]);

            // $masterLastBalance = MasterWalletLedger::where('master_id', $master->id)
            //     ->latest('id')
            //     ->value('balance') ?? 0;
            // MasterWalletLedger::create([
            //     'master_id' => $master->id,
            //     'date' => now()->toDateString(),
            //     'amount' => $master_incentive_amount,
            //     'type' => 'in',
            //     'balance' => $masterLastBalance + $master_incentive_amount,
            //     'source_type' => 'user_deposit_request',
            //     'source_id' => $data->id,
            // ]);
            // $this->updateDailySummaryForMaster(
            //     $master->id,
            //     'in',
            //     $master_incentive_amount,
            //     $masterLastBalance + $master_incentive_amount
            // );

            // // add data into report
            // $this->putDataIntoReport($data->amount, 'request_deposit');
            // $this->putDataIntoReportForMasterDepositCommission($master_incentive_amount, $master->id);
            // $this->putDataIntoReportForAgentDepositCommission($agent_incentive_amount, $agent->id);

            // $origin_data = UserDepositRequest::find($data->id);
            // Notification::create([
            //     'recipient_id' => $origin_data->user_id,
            //     'recipient_type' => 'user',
            //     'type' => 'approved_deposit',
            //     'title' => 'Deposit',
            //     'message' => 'Your Deposit is confirmed',
            //     'data' => $origin_data,
            // ]);
            // SendFcmToSingleReceiverJob::dispatch($origin_data->user_id, 'user', 'Deposit', 'Your Deposit is confirmed');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve user deposit request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectDepositRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'rejected',
                'action_by' => auth()->user()->id,
                'reason_for_rejection' => $reason
            ]);

            // $origin_data = UserDepositRequest::find($data->id);
            // Notification::create([
            //     'recipient_id' => $origin_data->user_id,
            //     'recipient_type' => 'user',
            //     'type' => 'rejected_deposit',
            //     'title' => 'Deposit',
            //     'message' => 'Your Deposit is cancelled',
            //     'data' => $origin_data,
            // ]);
            // SendFcmToSingleReceiverJob::dispatch($origin_data->user_id, 'user', 'Deposit', 'Your Deposit is cancelled');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject user deposit request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createDepositManually($data)
    {
        DB::beginTransaction();
        try {
            $user = User::find($data['user_id']);
            $agent = $user->agent;
            $master = $user->agent->master;

            $user->deposit($data['amount']);
            $manual_deposit = ManualUserDepositRecord::create([
                'user_id' => $data['user_id'],
                'amount' => $data['amount'],
                'action_by' => auth()->user()->id
            ]);

            // // agent incentive section
            // $agent_incentive_amount = $data['amount'] * ($agent->incentive_percentage / 100);
            // $agent->deposit($agent_incentive_amount);
            // $month = now()->format('Y-m');

            // AgentIncentive::create([
            //     'date_time' => now(),
            //     'month' => $month,
            //     'user_id' => $user->id,
            //     'agent_id' => $agent->id,
            //     'agent_code' => $user->agent_code,
            //     'deposit_amount' => $data['amount'],
            //     'incentive_percentage' => $agent->incentive_percentage,
            //     'incentive_amount' => $agent_incentive_amount,
            // ]);
            // $lastBalance = AgentWalletLedger::where('agent_id', $agent->id)
            //     ->latest('id')
            //     ->value('balance') ?? 0;

            // AgentWalletLedger::create([
            //     'agent_id' => $agent->id,
            //     'date' => now()->toDateString(),
            //     'amount' => $agent_incentive_amount,
            //     'type' => 'in',
            //     'balance' => $lastBalance + $agent_incentive_amount,
            //     'source_type' => 'manual_deposit',
            //     'source_id' => $manual_deposit->id,
            // ]);

            // $this->updateDailySummary(
            //     $agent->id,
            //     'in',
            //     $agent_incentive_amount,
            //     $lastBalance + $agent_incentive_amount
            // );

            // $this->updateMonthlySummary(
            //     $agent->id,
            //     $data['amount'],
            //     $agent_incentive_amount
            // );

            // // master incentive section
            // $master_incentive = $master->incentive_percentage - $agent->incentive_percentage;
            // $master_incentive_amount = $data['amount'] * ($master_incentive / 100);
            // $master->deposit($master_incentive_amount);
            // MasterIncentive::create([
            //     'date_time' => now(),
            //     'month' => $month,
            //     'user_id' => $user->id,
            //     'agent_id' => $agent->id,
            //     'master_id' => $master->id,
            //     'deposit_amount' => $data['amount'],
            //     'incentive_percentage' => $master_incentive,
            //     'incentive_amount' => $master_incentive_amount,
            // ]);

            // $masterLastBalance = MasterWalletLedger::where('master_id', $master->id)
            //     ->latest('id')
            //     ->value('balance') ?? 0;
            // MasterWalletLedger::create([
            //     'master_id' => $master->id,
            //     'date' => now()->toDateString(),
            //     'amount' => $master_incentive_amount,
            //     'type' => 'in',
            //     'balance' => $masterLastBalance + $master_incentive_amount,
            //     'source_type' => 'manual_deposit',
            //     'source_id' => $manual_deposit->id,
            // ]);
            // $this->updateDailySummaryForMaster(
            //     $master->id,
            //     'in',
            //     $master_incentive_amount,
            //     $masterLastBalance + $master_incentive_amount
            // );

            // // add data into report
            // $this->putDataIntoReport($data['amount'], 'manual_deposit');
            // $this->putDataIntoReportForMasterDepositCommission($master_incentive_amount, $master->id);
            // $this->putDataIntoReportForAgentDepositCommission($agent_incentive_amount, $agent->id);

            // $origin_data = ManualUserDepositRecord::find($manual_deposit->id);
            // Notification::create([
            //     'recipient_id' => $origin_data->user_id,
            //     'recipient_type' => 'user',
            //     'type' => 'manual_deposit',
            //     'title' => 'Manual Deposit',
            //     'message' => $origin_data->amount . ' MMK has been added into your wallet.',
            //     'data' => $origin_data,
            // ]);
            // SendFcmToSingleReceiverJob::dispatch($origin_data->user_id, 'user', 'Manual Deposit', $origin_data->amount . ' MMK has been added into your wallet.');

            DB::commit();
            $msg = 'Deposit of ' . $data['amount'] . ' MMK added to user: ' . $user->name . '. New balance: ' . $user->balance . ' MMK.';
            return $msg;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject user deposit request: ' . $e->getMessage());
            throw $e;
        }
    }

    private function updateDailySummary(
        int $agentId,
        string $type,
        float $amount,
        float $newBalance
    ) {
        if ($amount <= 0) {
            return;
        }

        $date = now()->toDateString();

        DB::transaction(function () use ($agentId, $type, $amount, $newBalance, $date) {
            $summary = AgentWalletDailySummary::where('agent_id', $agentId)
                ->where('date', $date)
                ->lockForUpdate()
                ->first();

            if (!$summary) {
                $openingBalance = $type === 'in'
                    ? $newBalance - $amount
                    : $newBalance + $amount;

                $summary = AgentWalletDailySummary::create([
                    'agent_id' => $agentId,
                    'date' => $date,
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $newBalance,
                    'total_in' => 0,
                    'total_out' => 0,
                ]);
            }

            if ($type === 'in') {
                $summary->increment('total_in', $amount);
            } else {
                $summary->increment('total_out', $amount);
            }

            $summary->update(['closing_balance' => $newBalance]);
        });
    }

    private function updateMonthlySummary(
        int $agentId,
        float $depositAmount,
        float $incentiveAmount,
    ) {
        if ($depositAmount <= 0 && $incentiveAmount <= 0) {
            return;
        }

        $dateTime = now();
        $month = $dateTime->format('Y-m');

        DB::transaction(function () use ($agentId, $month, $depositAmount, $incentiveAmount) {
            $summary = AgentIncentiveMonthlySummary::where('agent_id', $agentId)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (!$summary) {
                $summary = AgentIncentiveMonthlySummary::create([
                    'agent_id' => $agentId,
                    'month' => $month,
                    'total_deposit_amount' => 0,
                    'total_incentive_amount' => 0,
                ]);
            }

            if ($depositAmount > 0) {
                $summary->increment('total_deposit_amount', $depositAmount);
            }

            if ($incentiveAmount > 0) {
                $summary->increment('total_incentive_amount', $incentiveAmount);
            }
        });
    }

    private function updateDailySummaryForMaster(
        int $masterId,
        string $type,
        float $amount,
        float $newBalance
    ) {
        if ($amount <= 0) {
            return;
        }

        $date = now()->toDateString();

        DB::transaction(function () use ($masterId, $type, $amount, $newBalance, $date) {
            $summary = MasterWalletDailySummary::where('master_id', $masterId)
                ->where('date', $date)
                ->lockForUpdate()
                ->first();

            if (!$summary) {
                $openingBalance = $type === 'in'
                    ? $newBalance - $amount
                    : $newBalance + $amount;

                $summary = MasterWalletDailySummary::create([
                    'master_id' => $masterId,
                    'date' => $date,
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $newBalance,
                    'total_in' => 0,
                    'total_out' => 0,
                ]);
            }

            if ($type === 'in') {
                $summary->increment('total_in', $amount);
            } else {
                $summary->increment('total_out', $amount);
            }

            $summary->update(['closing_balance' => $newBalance]);
        });
    }

    private function putDataIntoReport($depositAmount, $target)
    {
        $report = DailyDepositReport::firstOrCreate(
            ['report_date' => today()],
            [
                'request_deposit' => 0,
                'manual_deposit' => 0,
                'total_deposit' => 0
            ]
        );

        if ($target == 'request_deposit') {
            $report->increment('request_deposit', $depositAmount);
        } else {
            $report->increment('manual_deposit', $depositAmount);
        }

        $report->increment('total_deposit', $depositAmount);
    }

    private function putDataIntoReportForMasterDepositCommission($commissionAmount, $masterId)
    {
        $report = DailyMasterDepositCommissionReport::firstOrCreate(
            [
                'report_date' => today(),
                'master_id' => $masterId
            ],
            [
                'deposit_commission_amount' => 0
            ]
        );

        $report->increment('deposit_commission_amount', $commissionAmount);
    }

    private function putDataIntoReportForAgentDepositCommission($commissionAmount, $agentId)
    {
        $report = DailyAgentDepositCommissionReport::firstOrCreate(
            [
                'report_date' => today(),
                'agent_id' => $agentId
            ],
            [
                'deposit_commission_amount' => 0
            ]
        );

        $report->increment('deposit_commission_amount', $commissionAmount);
    }
}
