<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();
            $t->string('name');
            $t->timestamps();
        });

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->string('role', 20)->default('student')->index();
            $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $t->string('student_no', 40)->nullable();
            $t->string('phone', 40)->nullable();
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });

        Schema::create('role_permissions', function (Blueprint $t) {
            $t->id();
            $t->string('role', 20)->index();
            $t->string('permission', 60);
            $t->unique(['role', 'permission']);
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key', 80)->unique();
            $t->text('value')->nullable();
        });

        Schema::create('phases', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedInteger('position')->default(1);
            $t->timestamps();
        });

        Schema::create('milestones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('phase_id')->constrained('phases')->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->unsignedInteger('position')->default(1);
            $t->date('due_date')->nullable();
            $t->timestamps();
        });

        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('abstract')->nullable();
            $t->string('type', 20)->default('thesis');
            $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $t->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('adviser_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status', 20)->default('ongoing');
            $t->date('start_date')->nullable();
            $t->date('due_date')->nullable();
            $t->timestamps();
        });

        Schema::create('project_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['project_id', 'user_id']);
        });

        Schema::create('project_milestones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $t->foreignId('milestone_id')->constrained('milestones')->cascadeOnDelete();
            $t->string('status', 20)->default('pending');
            $t->date('due_date')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->unique(['project_id', 'milestone_id']);
        });

        Schema::create('tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $t->foreignId('project_milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->date('due_date')->nullable();
            $t->string('status', 20)->default('todo');
            $t->timestamps();
        });

        Schema::create('submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $t->foreignId('project_milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('type', 20)->default('requirement');
            $t->unsignedTinyInteger('chapter_no')->nullable();
            $t->string('title');
            $t->unsignedInteger('version')->default(1);
            $t->string('file_path')->nullable();
            $t->string('original_name')->nullable();
            $t->string('status', 30)->default('submitted');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('feedback', function (Blueprint $t) {
            $t->id();
            $t->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('kind', 30)->default('comment');
            $t->text('body')->nullable();
            $t->timestamps();
        });

        Schema::create('notices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('title');
            $t->text('body')->nullable();
            $t->string('url')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 60);
            $t->text('description')->nullable();
            $t->string('ip', 60)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'notices', 'feedback', 'submissions', 'tasks', 'project_milestones', 'project_members', 'projects', 'milestones', 'phases', 'settings', 'role_permissions', 'password_reset_tokens', 'users', 'departments'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
