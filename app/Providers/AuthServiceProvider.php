<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\FileAttachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Policies\CommentPolicy;
use App\Policies\FileAttachmentPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        Comment::class => CommentPolicy::class,
        FileAttachment::class => FileAttachmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
