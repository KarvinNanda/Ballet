<?php

namespace App\Http\Requests\Staff;

use App\Http\Requests\SearchRequest;

class StudentListRequest extends SearchRequest
{
    protected function choices(): array
    {
        return ['status' => ['all', 'aktif', 'non-aktif', 'trial']];
    }
}
