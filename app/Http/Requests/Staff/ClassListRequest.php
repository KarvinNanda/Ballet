<?php

namespace App\Http\Requests\Staff;

use App\Http\Requests\SearchRequest;

class ClassListRequest extends SearchRequest
{
    protected function choices(): array
    {
        return ['status' => ['all', 'aktif', 'non-aktif']];
    }
}
