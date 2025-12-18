<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->middleware('guest');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::get('/notifications/preferences', [\App\Http\Controllers\NotificationPreferenceController::class, 'index'])->name('notifications.preferences');
    Route::put('/notifications/preferences', [\App\Http\Controllers\NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
    Route::put('/notifications/preferences/{type}', [\App\Http\Controllers\NotificationPreferenceController::class, 'updateSingle'])->name('notifications.preferences.update-single');

    // Super Admin Panel
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    });

    // Users Management (Solo Super Admins)
    // La verificación se hace en UserPolicy y UserController
    Route::resource('users', UserController::class);

    // Teams
    Route::resource('teams', \App\Http\Controllers\TeamController::class);
    Route::prefix('teams/{team}')->name('teams.')->group(function () {
        Route::get('members', [\App\Http\Controllers\TeamController::class, 'members'])->name('members');
        Route::post('members', [\App\Http\Controllers\TeamController::class, 'addMember'])->name('members.add');
        Route::put('members/{user}', [\App\Http\Controllers\TeamController::class, 'updateMemberRole'])->name('members.update');
        Route::delete('members/{user}', [\App\Http\Controllers\TeamController::class, 'removeMember'])->name('members.remove');
    });

    // Invitations
    Route::prefix('invitations')->name('invitations.')->group(function () {
        Route::post('teams/{team}', [\App\Http\Controllers\InvitationController::class, 'inviteToTeam'])->name('teams.invite');
        Route::post('projects/{project}', [\App\Http\Controllers\InvitationController::class, 'inviteToProject'])->name('projects.invite');
        Route::get('accept/{token}', [\App\Http\Controllers\InvitationController::class, 'accept'])->name('accept');
        Route::post('reject/{token}', [\App\Http\Controllers\InvitationController::class, 'reject'])->name('reject');
        Route::delete('{invitation}', [\App\Http\Controllers\InvitationController::class, 'cancel'])->name('cancel');
    });

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::get('projects/{project}/kanban', [ProjectController::class, 'kanban'])->name('projects.kanban');
    Route::get('projects/{project}/gallery', [\App\Http\Controllers\ProjectGalleryController::class, 'index'])->name('projects.gallery');
    
    // Project Statuses
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        Route::get('statuses', [\App\Http\Controllers\TaskStatusController::class, 'index'])->name('statuses.index');
        Route::get('statuses/create', [\App\Http\Controllers\TaskStatusController::class, 'create'])->name('statuses.create');
        Route::post('statuses', [\App\Http\Controllers\TaskStatusController::class, 'store'])->name('statuses.store');
        Route::get('statuses/{taskStatus}/edit', [\App\Http\Controllers\TaskStatusController::class, 'edit'])->name('statuses.edit');
        Route::put('statuses/{taskStatus}', [\App\Http\Controllers\TaskStatusController::class, 'update'])->name('statuses.update');
        Route::delete('statuses/{taskStatus}', [\App\Http\Controllers\TaskStatusController::class, 'destroy'])->name('statuses.destroy');
        Route::post('statuses/reorder', [\App\Http\Controllers\TaskStatusController::class, 'reorder'])->name('statuses.reorder');

        // Tasks
        Route::get('tasks', [\App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/create', [\App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
        Route::post('tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
        Route::get('tasks/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
        Route::get('tasks/{task}/edit', [\App\Http\Controllers\TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
        Route::delete('tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('tasks/{task}/move', [\App\Http\Controllers\TaskController::class, 'move'])->name('tasks.move');
        Route::get('tasks/{task}/blocking', [\App\Http\Controllers\TaskController::class, 'blocking'])->name('tasks.blocking');
        Route::get('tasks/{task}/movements', [\App\Http\Controllers\TaskController::class, 'movements'])->name('tasks.movements');
        Route::post('tasks/reorder', [\App\Http\Controllers\TaskController::class, 'reorder'])->name('tasks.reorder');

        // Comments
        Route::post('tasks/{task}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('tasks.comments.store');
        Route::put('tasks/{task}/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'update'])->name('tasks.comments.update');
        Route::delete('tasks/{task}/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy'])->name('tasks.comments.destroy');

        // Realtime
        Route::post('realtime/typing', [\App\Http\Controllers\RealtimeController::class, 'typing'])->name('realtime.typing');
        Route::post('realtime/cursor', [\App\Http\Controllers\RealtimeController::class, 'cursor'])->name('realtime.cursor');

        // Activity Log
        Route::get('activity', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity.index');

        // File Attachments
        Route::post('attachments', [\App\Http\Controllers\FileAttachmentController::class, 'store'])->name('attachments.store');
        Route::get('attachments/{fileAttachment}', [\App\Http\Controllers\FileAttachmentController::class, 'show'])->name('attachments.show');
        Route::get('attachments/{fileAttachment}/download', [\App\Http\Controllers\FileAttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('attachments/{fileAttachment}', [\App\Http\Controllers\FileAttachmentController::class, 'destroy'])->name('attachments.destroy');

        // Project Members
        Route::get('members', [\App\Http\Controllers\ProjectController::class, 'members'])->name('members');
        Route::post('members', [\App\Http\Controllers\ProjectController::class, 'addMember'])->name('members.add');
        Route::put('members/{user}', [\App\Http\Controllers\ProjectController::class, 'updateMemberRole'])->name('members.update');
        Route::delete('members/{user}', [\App\Http\Controllers\ProjectController::class, 'removeMember'])->name('members.remove');
    });
});

require __DIR__.'/auth.php';
