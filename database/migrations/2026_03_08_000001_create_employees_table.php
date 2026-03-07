<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->date('hired_at');
            $table->date('date_of_birth')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->text('address')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('national_id')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('bank_account')->nullable();
            $table->tinyInteger('contract_type')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->tinyInteger('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('photo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
