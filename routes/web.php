<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('landing'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Role landing pages (Student Member and Student Project Manager each get their own).
    Route::get('/member', [DashboardController::class, 'member'])->middleware('role:student')->name('member.home');
    Route::get('/manager', [DashboardController::class, 'manager'])->middleware('role:leader')->name('manager.home');
    Route::get('/instructor', [DashboardController::class, 'instructor'])->middleware('role:instructor')->name('instructor.home');
    Route::get('/adviser', [DashboardController::class, 'adviser'])->middleware('role:adviser')->name('adviser.home');
    Route::get('/coordinator', [DashboardController::class, 'coordinator'])->middleware('role:coordinator')->name('coordinator.home');
    Route::get('/admin', [DashboardController::class, 'admin'])->middleware('role:admin')->name('admin.home');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->middleware('perm:projects.create')->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->middleware('perm:projects.create')->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.add');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');
    Route::post('/projects/{project}/updates', [ProjectController::class, 'storeUpdate'])->name('projects.updates.store');
    Route::patch('/milestones/{pm}', [MilestoneController::class, 'update'])->name('milestones.update');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('perm:tasks.manage')->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware('perm:tasks.manage')->name('tasks.destroy');

    // Submissions
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/create', [SubmissionController::class, 'create'])->middleware('perm:submissions.upload')->name('submissions.create');
    Route::post('/submissions', [SubmissionController::class, 'store'])->middleware('perm:submissions.upload')->name('submissions.store');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
    Route::post('/submissions/{submission}/review', [SubmissionController::class, 'review'])->name('submissions.review');

    // Chapters and manuscripts, organized by department
    Route::get('/library/{kind}', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/{kind}/{department:code}', [LibraryController::class, 'department'])->name('library.department');

    // Notifications and profile
    Route::get('/notifications', [NoticeController::class, 'index'])->name('notices.index');
    Route::post('/notifications/read-all', [NoticeController::class, 'readAll'])->name('notices.readall');
    Route::get('/notifications/{notice}/open', [NoticeController::class, 'open'])->name('notices.open');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    // Project Coordinator tools
    Route::prefix('coordinator')->group(function () {
        Route::get('/phases', [CoordinatorController::class, 'phases'])->middleware('perm:phases.manage')->name('coordinator.phases');
        Route::post('/phases', [CoordinatorController::class, 'storePhase'])->middleware('perm:phases.manage')->name('coordinator.phases.store');
        Route::delete('/phases/{phase}', [CoordinatorController::class, 'destroyPhase'])->middleware('perm:phases.manage')->name('coordinator.phases.destroy');
        Route::post('/milestones', [CoordinatorController::class, 'storeMilestone'])->middleware('perm:phases.manage')->name('coordinator.milestones.store');
        Route::put('/milestones/{milestone}', [CoordinatorController::class, 'updateMilestone'])->middleware('perm:phases.manage')->name('coordinator.milestones.update');
        Route::delete('/milestones/{milestone}', [CoordinatorController::class, 'destroyMilestone'])->middleware('perm:phases.manage')->name('coordinator.milestones.destroy');

        Route::get('/assignments', [CoordinatorController::class, 'assignments'])->middleware('perm:assignments.manage')->name('coordinator.assignments');
        Route::put('/assignments/{project}', [CoordinatorController::class, 'assign'])->middleware('perm:assignments.manage')->name('coordinator.assign');

        Route::get('/delayed', [CoordinatorController::class, 'delayed'])->middleware('perm:reports.view')->name('coordinator.delayed');
        Route::get('/reports', [CoordinatorController::class, 'reports'])->middleware('perm:reports.view')->name('coordinator.reports');
        Route::get('/reports/export', [CoordinatorController::class, 'export'])->middleware('perm:reports.view')->name('coordinator.export');
    });

    // Administrator tools
    Route::prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->middleware('perm:users.manage')->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser'])->middleware('perm:users.manage')->name('admin.users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->middleware('perm:users.manage')->name('admin.users.update');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->middleware('perm:users.manage')->name('admin.users.toggle');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->middleware('perm:users.manage')->name('admin.users.destroy');

        Route::get('/ids', [AdminController::class, 'ids'])->middleware('perm:users.manage')->name('admin.ids');
        Route::post('/ids', [AdminController::class, 'storeId'])->middleware('perm:users.manage')->name('admin.ids.store');
        Route::post('/ids/import', [AdminController::class, 'importIds'])->middleware('perm:users.manage')->name('admin.ids.import');
        Route::delete('/ids/{registeredId}', [AdminController::class, 'destroyId'])->middleware('perm:users.manage')->name('admin.ids.destroy');

        Route::get('/roles', [AdminController::class, 'roles'])->middleware('perm:roles.manage')->name('admin.roles');
        Route::put('/roles', [AdminController::class, 'saveRoles'])->middleware('perm:roles.manage')->name('admin.roles.save');

        Route::get('/settings', [AdminController::class, 'settings'])->middleware('perm:settings.manage')->name('admin.settings');
        Route::put('/settings', [AdminController::class, 'saveSettings'])->middleware('perm:settings.manage')->name('admin.settings.save');
        Route::post('/departments', [AdminController::class, 'storeDepartment'])->middleware('perm:settings.manage')->name('admin.departments.store');
        Route::delete('/departments/{department}', [AdminController::class, 'destroyDepartment'])->middleware('perm:settings.manage')->name('admin.departments.destroy');

        Route::get('/records', [AdminController::class, 'records'])->middleware('perm:records.manage')->name('admin.records');
        Route::delete('/records/projects/{project}', [AdminController::class, 'destroyProject'])->middleware('perm:records.manage')->name('admin.records.project');
        Route::delete('/records/submissions/{submission}', [AdminController::class, 'destroySubmission'])->middleware('perm:records.manage')->name('admin.records.submission');

        Route::get('/reports', [AdminController::class, 'reports'])->middleware('perm:reports.system')->name('admin.reports');
        Route::get('/logs', [AdminController::class, 'logs'])->middleware('perm:logs.view')->name('admin.logs');
    });
});
