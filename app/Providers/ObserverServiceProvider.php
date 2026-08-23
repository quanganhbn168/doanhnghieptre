<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Observers\SlugObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
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
        // Đăng ký SlugObserver cho các Model liên quan
        Page::observe(SlugObserver::class);
        Post::observe(SlugObserver::class);
        PostCategory::observe(SlugObserver::class);
        Service::observe(SlugObserver::class);
        Project::observe(SlugObserver::class);
        Client::observe(SlugObserver::class);
        ServiceCategory::observe(SlugObserver::class);
        ProjectCategory::observe(SlugObserver::class);
    }
}
