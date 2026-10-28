<?php

namespace App\Providers;

use App\Models\BusinessHour;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

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
        View::composer('layouts.app', function (ViewInstance $view): void {
            $viewData = $view->getData();
            $footerHours = $viewData['hours'] ?? BusinessHour::query()
                ->orderBy('weekday')
                ->get();

            $view->with('footerHours', $footerHours);
        });

        VerifyEmail::createUrlUsing(function (User $user): string {
            return URL::temporarySignedRoute(
                $user->isAdmin() ? 'admin.email.verify' : 'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ],
            );
        });
    }
}
