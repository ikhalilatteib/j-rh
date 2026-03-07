<?php

namespace Ikay\JRh\Models;

use Ikay\JRh\Database\Factories\SalaryFactory;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    /** @use HasFactory<SalaryFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'base_salary',
        'prime',
        'advance_deductions',
        'net_salary',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SalaryStatusEnum::class,
            'base_salary' => 'decimal:2',
            'prime' => 'decimal:2',
            'advance_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    protected static function newFactory(): SalaryFactory
    {
        return SalaryFactory::new();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate and set the net salary.
     */
    public function calculateNetSalary(): void
    {
        $this->net_salary = $this->base_salary + $this->prime - $this->advance_deductions;
    }
}
