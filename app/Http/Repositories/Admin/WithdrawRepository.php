<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;

use App\Jobs\SendFcmToSingleReceiverJob;
use App\Models\AgentWalletDailySummary;
use App\Models\AgentWalletLedger;
use App\Models\AgentWithdrawRequest;
use App\Models\ManualUserWithdrawRecord;
use App\Models\MasterWalletDailySummary;
use App\Models\MasterWalletLedger;
use App\Models\MasterWithdrawRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserWithdrawRequest;
use Exception;
use Illuminate\Support\Facades\DB;

class WithdrawRepository extends BaseRepo
{
    public function __construct(UserWithdrawRequest $model)
    {
        parent::__construct($model);
    }

    public function getManualWithdrawLists($page, $per_page, $with, $searches = null)
    {
        $query = ManualUserWithdrawRecord::with($with)
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

    public function findWithdrawRequest($id)
    {
        $data = UserWithdrawRequest::with(['user', 'actionBy', 'paymentMethod'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function whereLatest($column, $value)
    {
        $data = UserWithdrawRequest::where($column, $value)->latest()->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function approveWithdrawRequest($data)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'approved',
                'action_by' => auth()->user()->id,
            ]);
            $data->user->withdraw($data->amount);

            // $origin_data = UserWithdrawRequest::find($data->id);

            // Notification::create([
            //     'recipient_id' => $origin_data->user_id,
            //     'recipient_type' => 'user',
            //     'type' => 'approved_withdraw',
            //     'title' => 'Withdrawl',
            //     'message' => 'Your Withdrawl is confirmed',
            //     'data' => $origin_data,
            // ]);
            // SendFcmToSingleReceiverJob::dispatch($origin_data->user_id, 'user', 'Withdrawl', 'Your Withdrawl is confirmed');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve user withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectWithdrawRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'rejected',
                'action_by' => auth()->user()->id,
                'reason_for_rejection' => $reason
            ]);

            // $origin_data = UserWithdrawRequest::find($data->id);
            // Notification::create([
            //     'recipient_id' => $origin_data->user_id,
            //     'recipient_type' => 'user',
            //     'type' => 'rejected_withdraw',
            //     'title' => 'Withdrawl',
            //     'message' => 'Your Withdrawl is cancelled',
            //     'data' => $origin_data,
            // ]);
            // SendFcmToSingleReceiverJob::dispatch($origin_data->user_id, 'user', 'Withdrawl', 'Your Withdrawl is cancelled');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject user withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createWithdrawManually($data)
    {
        DB::beginTransaction();
        try {
            $user = User::find($data['user_id']);
            $user->withdraw($data['amount']);
            $manual_withdraw = ManualUserWithdrawRecord::create([
                'user_id' => $data['user_id'],
                'amount' => $data['amount'],
                'action_by' => auth()->user()->id
            ]);

            // $origin_data = ManualUserWithdrawRecord::find($manual_withdraw->id);
            // Notification::create([
            //     'recipient_id' => $origin_data->user_id,
            //     'recipient_type' => 'user',
            //     'type' => 'manual_withdraw',
            //     'title' => 'Manual Withdrawl',
            //     'message' => $origin_data->amount . ' MMK has been withdrawed from your wallet.',
            //     'data' => $origin_data,
            // ]);
            // SendFcmToSingleReceiverJob::dispatch($origin_data->user_id, 'user', 'Manual Withdrawl', $origin_data->amount . ' MMK has been withdrawed from your wallet.');

            DB::commit();
            $msg = 'Withdraw of ' . $data['amount'] . ' MMK out from user: ' . $user->name . '. New balance: ' . $user->balance . ' MMK.';
            return $msg;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject user withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAgentRequestData($page, $per_page, $with, $searches = null)
    {
        $query = AgentWithdrawRequest::with($with)
            ->orderByDesc('created_at');

        if (!empty($searches)) {
            $query->whereHas('agent', function ($q) use ($searches) {
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

    public function findAgentWithdrawRequest($id)
    {
        $data = AgentWithdrawRequest::with(['agent', 'actionBy', 'paymentMethod'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function approveAgentWithdrawRequest($data)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'approved',
                'action_by' => auth()->user()->id,
            ]);
            $data->agent->withdraw($data->amount);

            $lastBalance = AgentWalletLedger::where('agent_id', $data->agent->id)
                ->latest('id')
                ->value('balance') ?? 0;

            AgentWalletLedger::create([
                'agent_id' => $data->agent->id,
                'date' => now()->toDateString(),
                'amount' => $data->amount,
                'type' => 'out',
                'balance' => $lastBalance - $data->amount,
                'source_type' => 'agent_withdraw_request',
                'source_id' => $data->id,
            ]);

            $this->updateDailySummary(
                $data->agent->id,
                'out',
                $data->amount,
                $lastBalance - $data->amount
            );

            $origin_data = AgentWithdrawRequest::find($data->id);
            Notification::create([
                'recipient_id' => $origin_data->agent_id,
                'recipient_type' => 'agent',
                'type' => 'approved_withdraw',
                'title' => 'Withdrawl',
                'message' => 'Your Withdrawl is confirmed',
                'data' => $origin_data,
            ]);
            SendFcmToSingleReceiverJob::dispatch($origin_data->agent_id, 'agent', 'Withdrawl', 'Your Withdrawl is confirmed');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve agent withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectAgentWithdrawRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'rejected',
                'action_by' => auth()->user()->id,
                'reason_for_rejection' => $reason
            ]);

            $origin_data = AgentWithdrawRequest::find($data->id);
            Notification::create([
                'recipient_id' => $origin_data->agent_id,
                'recipient_type' => 'agent',
                'type' => 'rejected_withdraw',
                'title' => 'Withdrawl',
                'message' => 'Your Withdrawl is cancelled',
                'data' => $origin_data,
            ]);
            SendFcmToSingleReceiverJob::dispatch($origin_data->agent_id, 'agent', 'Withdrawl', 'Your Withdrawl is cancelled');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject agent withdraw request: ' . $e->getMessage());
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

    public function findAgentWithdraw($id)
    {
        $data = AgentWithdrawRequest::with(['agent', 'actionBy', 'paymentMethod'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function getMasterRequestData($page, $per_page, $with, $searches = null)
    {
        $query = MasterWithdrawRequest::with($with)
            ->orderByDesc('created_at');

        if (!empty($searches)) {
            $query->whereHas('master', function ($q) use ($searches) {
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

    public function findMasterWithdrawRequest($id)
    {
        $data = MasterWithdrawRequest::with(['master', 'actionBy', 'paymentMethod'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function approveMasterWithdrawRequest($data)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'approved',
                'action_by' => auth()->user()->id,
            ]);
            $data->master->withdraw($data->amount);

            $lastBalance = MasterWalletLedger::where('master_id', $data->master->id)
                ->latest('id')
                ->value('balance') ?? 0;

            MasterWalletLedger::create([
                'master_id' => $data->master->id,
                'date' => now()->toDateString(),
                'amount' => $data->amount,
                'type' => 'out',
                'balance' => $lastBalance - $data->amount,
                'source_type' => 'master_withdraw_request',
                'source_id' => $data->id,
            ]);

            $this->updateDailySummaryForMaster(
                $data->master->id,
                'out',
                $data->amount,
                $lastBalance - $data->amount
            );

            $origin_data = MasterWithdrawRequest::find($data->id);
            Notification::create([
                'recipient_id' => $origin_data->master_id,
                'recipient_type' => 'master',
                'type' => 'approved_withdraw',
                'title' => 'Withdrawl',
                'message' => 'Your Withdrawl is confirmed',
                'data' => $origin_data,
            ]);
            SendFcmToSingleReceiverJob::dispatch($origin_data->master_id, 'master', 'Withdrawl', 'Your Withdrawl is confirmed');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve master withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectMasterWithdrawRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $data->update([
                'status' => 'rejected',
                'action_by' => auth()->user()->id,
                'reason_for_rejection' => $reason
            ]);

            $origin_data = MasterWithdrawRequest::find($data->id);
            Notification::create([
                'recipient_id' => $origin_data->master_id,
                'recipient_type' => 'master',
                'type' => 'rejected_withdraw',
                'title' => 'Withdrawl',
                'message' => 'Your Withdrawl is cancelled',
                'data' => $origin_data,
            ]);
            SendFcmToSingleReceiverJob::dispatch($origin_data->master_id, 'master', 'Withdrawl', 'Your Withdrawl is cancelled');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject master withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findMasterWithdraw($id)
    {
        $data = MasterWithdrawRequest::with(['master', 'actionBy', 'paymentMethod'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }
}
