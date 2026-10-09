<?php

namespace App\Http\Requests\Staff;

use App\Http\Requests\SearchRequest;

class TransactionListRequest extends SearchRequest
{
    protected function choices(): array
    {
        return ['status' => ['all', 'Unpaid', 'Paid']];
    }
}
