<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\FieldAssignment;
use App\Models\Notification;
use App\Models\Project;
use App\Policies\ComplaintPolicy;
use App\Policies\FieldAssignmentPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(FieldAssignment::class, FieldAssignmentPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
    }
}
