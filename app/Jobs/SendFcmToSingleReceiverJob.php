<?php

namespace App\Jobs;

use App\Models\UserFcmToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendFcmToSingleReceiverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipientId;
    protected $recipientType;
    protected $title;
    protected $body;
    protected $data;

    public function __construct($recipientId, $recipientType, $title, $body, $data = [])
    {
        $this->recipientId = $recipientId;
        $this->recipientType = $recipientType;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function handle()
    {
        $tokens = UserFcmToken::where('recipient_id', $this->recipientId)
            ->where('recipient_type', $this->recipientType)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->send($tokens);
    }

    private function send(array $tokens)
    {
        $messaging = Firebase::messaging();

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($this->title, $this->body))
                    ->withData($this->formatData($this->data));

                $response = $messaging->send($message);
                Log::info('FCM sent successfully', [
                    'token' => $token,
                    'data' => $this->formatData($this->data),
                    'response' => $response
                ]);
            } catch (\Exception $e) {
                Log::error('FCM failed', [
                    'token' => $token,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function formatData($data): array
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        $formatted = [];

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $formatted[$key] = json_encode($value);
            } else {
                $formatted[$key] = (string) $value;
            }
        }

        return $formatted;
    }

}
