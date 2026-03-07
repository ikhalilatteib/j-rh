<?php

namespace Ikay\JRh\Traits;

use Ikay\JRh\Models\Employee;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasEmployees
{
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
