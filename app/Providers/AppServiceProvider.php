<?php

namespace App\Providers;

use App\Contracts\AiSuggestionServiceInterface;
use App\Models\User;
use App\Services\OpenAiSuggestionService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AiSuggestionServiceInterface::class, OpenAiSuggestionService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Gate::before(fn (User $user) => ! $user->is_active ? false : null);
        Gate::define('hod', fn (User $user) => $user->isHod());
        Gate::define('faculty', fn (User $user) => $user->isFaculty());
        Gate::define('professor', fn (User $user) => $user->isProfessor());
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('recovery', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('ai', fn (Request $request) => [Limit::perMinute(2)->by((string) $request->user()->id), Limit::perDay(20)->by((string) $request->user()->id)]);
    }
}
