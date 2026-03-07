<?php

namespace Ikay\JRh\Models;

use Ikay\JRh\Database\Factories\AdvanceFactory;
use Ikay\JRh\Enums\AdvanceStatusEnum;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advance extends Model
{
    /** @use HasFactory<AdvanceFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'amount',
        'date',
        'reason',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => AdvanceStatusEnum::class,
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    protected static function newFactory(): AdvanceFactory
    {
        return AdvanceFactory::new();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get total approved advances for an employee.
     */
    public static function totalApproved(int $employeeId): float
    {
        return (float) static::query()
            ->where('employee_id', $employeeId)
            ->where('status', AdvanceStatusEnum::Approved)
            ->sum('amount');
    }

    /**
     * Get total advance deductions already applied in non-cancelled salaries for an employee.
     */
    public static function totalDeducted(int $employeeId): float
    {
        return (float) Salary::query()
            ->where('employee_id', $employeeId)
            ->whereNot('status', SalaryStatusEnum::Cancelled)
            ->sum('advance_deductions');
    }

    /**
     * Get remaining outstanding advances for an employee (approved - already deducted).
     */
    public static function remainingOutstanding(int $employeeId): float
    {
        return max(0, static::totalApproved($employeeId) - static::totalDeducted($employeeId));
    }
}
