<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registered_ids', function (Blueprint $t) {
            $t->id();
            $t->string('id_number', 30)->unique();
            $t->string('type', 20)->index(); // student, instructor, adviser, coordinator
            $t->string('full_name')->nullable();
            $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $t->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('used_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registered_ids');
    }
};
