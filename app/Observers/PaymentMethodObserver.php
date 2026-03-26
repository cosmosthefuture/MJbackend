<?php

namespace App\Observers;

use App\Models\PaymentMethod;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

class PaymentMethodObserver
{
    protected function clearCache(): void
    {
        if (Cache::getStore() instanceof RedisStore) {
            Cache::tags(['payment_methods'])->flush();
        }
    }

    public function created(PaymentMethod $payment_method): void
    {
        $this->clearCache();
    }

    public function updated(PaymentMethod $payment_method): void
    {
        $this->clearCache();
    }

    public function deleted(PaymentMethod $payment_method): void
    {
        $this->clearCache();
    }

    public function restored(PaymentMethod $payment_method): void
    {
        $this->clearCache();
    }

    public function forceDeleted(PaymentMethod $payment_method): void
    {
        //
    }
}
