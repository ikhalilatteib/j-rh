<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('advances', 'user_id')) {
            return;
        }

        Schema::table('advances', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id');
        });

        $userIds = DB::table('advances')->distinct()->pluck('user_id')->filter();

        foreach ($userIds as $userId) {
            $employee = DB::table('employees')->where('user_id', $userId)->first();

            if (! $employee) {
                $user = DB::table('users')->find($userId);
                if (! $user) {
                    continue;
                }

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

            DB::table('advances')->where('user_id', $userId)->update(['employee_id' => $employeeId]);
        }

        Schema::table('advances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('advances', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable(false)->change();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('advances', 'employee_id')) {
            return;
        }

        Schema::table('advances', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id');
        });

        $employeeMap = DB::table('employees')->whereNotNull('user_id')->pluck('user_id', 'id');

        foreach ($employeeMap as $employeeId => $userId) {
            DB::table('advances')->where('employee_id', $employeeId)->update(['user_id' => $userId]);
        }

        Schema::table('advances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });

        Schema::table('advances', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
