<?php

namespace Ikay\JRh\Models;

use Ikay\JRh\Database\Factories\EmployeeFactory;
use Ikay\JRh\Enums\ContractTypeEnum;
use Ikay\JRh\Enums\EmployeeStatusEnum;
use Ikay\JRh\Enums\GenderEnum;
use Ikay\JRh\Enums\MaritalStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'position',
        'department',
        'hired_at',
        'date_of_birth',
        'gender',
        'address',
        'salary',
        'national_id',
        'emergency_contact',
        'bank_account',
        'contract_type',
        'contract_end_date',
        'marital_status',
        'nationality',
        'status',
        'photo',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => EmployeeStatusEnum::class,
            'gender' => GenderEnum::class,
            'contract_type' => ContractTypeEnum::class,
            'marital_status' => MaritalStatusEnum::class,
            'salary' => 'decimal:2',
            'hired_at' => 'date',
            'date_of_birth' => 'date',
            'contract_end_date' => 'date',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Employee $employee): void {
            if (empty($employee->employee_id)) {
                $prefix = config('j-rh.employee_id_prefix', 'EMP');
                $lastId = static::withTrashed()->max('id') ?? 0;
                $employee->employee_id = $prefix.'-'.str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('j-rh.user_model', \App\Models\User::class));
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class);
    }
}
