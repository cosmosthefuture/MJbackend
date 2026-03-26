<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;

use App\Jobs\SendFcmToSingleReceiverJob;
use App\Models\DailyMoneyTransferCommissionReport;
use App\Models\DailyProfitReport;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserMoneyTransferRecord;

use Exception;
use Illuminate\Support\Facades\DB;

class MoneyTransferRepository extends BaseRepo
{
    public function __construct(UserMoneyTransferRecord $model)
    {
        parent::__construct($model);
    }

    public function whereLatest($column, $value)
    {
        $data = UserMoneyTransferRecord::where($column, $value)->latest()->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function findUserByPhoneNumber($phone_number)
    {
        $data = User::where('phone_number', $phone_number)->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function findUser($id)
    {
        $data = User::find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function createMoneyTransfer($data)
    {
        $record = $this->model->create($data);
        $origin_record = $this->model->with(['recipient', 'sender'])->find($record->id);
        $sender = auth('api-user')->user();
        $sender->withdraw($data['amount'], ['type' => 'money_transfer', 'record_id' => $record->id]);
        $msg = $sender->name . " transfered " . $data['amount'] . " MMKs to you.";
        Notification::sendTo($record->recipient, 'money_transfer', 'Money Transfer', $msg, $origin_record);
        // SendFcmToSingleReceiverJob::dispatch($record->recipient->id, 'user', 'Money Transfer', $msg);
        return $record;
    }

    public function accept($record, $noti_id)
    {
        $record->status = 'accepted';
        $record->save();
        $record->recipient->deposit($record->amount, ['type' => 'user_money_transfer', 'record_id' => $record->id]);
        $record->recipient->withdraw($record->house_cut_amount, ['type' => 'money_transfer', 'record_id' => $record->id]);

        // report
        $this->putDataIntoMoneyTransferReport($record->house_cut_amount);
        $this->putDataIntoProfitReport($record->house_cut_amount, 'money_transfer');

        $msg = $record->recipient->name . " accepted your money transfer.";
        Notification::sendTo($record->sender, 'money_transfer_status_update', 'Money Transfer Status Update', $msg, $record);
        SendFcmToSingleReceiverJob::dispatch($record->sender->id, 'user', 'Money Transfer', $msg);
        $noti = Notification::find($noti_id);
        $data = $noti->data;
        $data['status'] = 'accepted';

        $noti->data = $data;
        $noti->save();
    }

    public function reject($record, $noti_id)
    {
        $record->status = 'rejected';
        $record->save();
        $record->sender->deposit($record->amount, ['type' => 'user_money_transfer_refunded', 'record_id' => $record->id]);
        $msg = $record->recipient->name . " rejected your money transfer. Transfered amount refunded to your wallet.";
        Notification::sendTo($record->sender, 'money_transfer_status_update', 'Money Transfer Status Update', $msg, $record);
        SendFcmToSingleReceiverJob::dispatch($record->sender->id, 'user', 'Money Transfer', $msg);
        $noti = Notification::find($noti_id);
        $data = $noti->data;
        $data['status'] = 'rejected';

        $noti->data = $data;
        $noti->save();
    }

    private function putDataIntoMoneyTransferReport($commissionAmount)
    {
        $report = DailyMoneyTransferCommissionReport::firstOrCreate(
            [
                'report_date' => today(),
            ],
            [
                'total_commission_amount' => 0
            ]
        );

        $report->increment('total_commission_amount', $commissionAmount);
    }

    private function putDataIntoProfitReport($amount, $target)
    {
        $report = DailyProfitReport::firstOrCreate(
            ['report_date' => today()],
            [
                'spin_wheel_profit' => 0,
                'coin_flip_profit' => 0,
                'money_transfer_profit' => 0,
                'total_profit' => 0
            ]
        );

        if ($target === 'spin_wheel') {
            $report->increment('spin_wheel_profit', $amount);
        } elseif ($target === 'coin_flip') {
            $report->increment('coin_flip_profit', $amount);
        } elseif ($target === 'money_transfer') {
            $report->increment('money_transfer_profit', $amount);
        }

        $report->increment('total_profit', $amount);
    }
}
