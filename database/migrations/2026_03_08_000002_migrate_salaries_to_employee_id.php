<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('salaries', 'user_id')) {
            return;
        }

        Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id');
        });

        $userIds = DB::table('salaries')->distinct()->pluck('user_id')->filter();

        foreach ($userIds as $userId) {
            $user = DB::table('users')->find($userId);
            if (! $user) {
                continue;
            }

            $employee = DB::table('employees')->where('user_id', $userId)->first();

            if (! $employee) {
                $prefix = config('j-rh.employee_id_prefix', 'EMP');
                $lastId = DB::table('employees')->max('id') ?? 0;
                $employeeIdCode = $prefix.'-'.str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);

                $employeeId = DB::table('employees')->insertGetId([
                    'employee_id' => $employeeIdCode,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? null,
                    'salary' => $user->salary ?? 0,
                    'hired_at' => $user->created_at,
                    'status' => 1,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $employeeId = $employee->id;
            }

            DB::table('salaries')->where('user_id', $userId)->update(['employee_id' => $employeeId]);
        }

        Schema::table('salaries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'month', 'year']);
            $table->dropColumn('user_id');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable(false)->change();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('salaries', 'employee_id')) {
            return;
        }

        Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id');
        });

        $employeeMap = DB::table('employees')->whereNotNull('user_id')->pluck('user_id', 'id');

        foreach ($employeeMap as $employeeId => $userId) {
            DB::table('salaries')->where('employee_id', $employeeId)->update(['user_id' => $userId]);
        }

        Schema::table('salaries', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'month', 'year']);
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'month', 'year']);
        });
    }
};
