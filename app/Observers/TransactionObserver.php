<?php

namespace App\Observers;
use App\Transaction;
use Uuid;
class TransactionObserver
{
    public function creating(Transaction $transaction)
    {
        $transaction->uuid = Uuid::generate()->string;
    }
}
