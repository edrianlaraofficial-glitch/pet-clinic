<?php

namespace App\Providers;

use App\Models\BusinessHour;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

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
        $brevoTransport = static fn (array $config): TransportInterface => (new BrevoTransportFactory(client: HttpClient::create()))
            ->create(Dsn::fromString($config['url']));

        Mail::extend('brevo', $brevoTransport);
        Mail::extend('brevo+api', $brevoTransport);

        View::composer('layouts.app', function (ViewInstance $view): void {
            $viewData = $view->getData();
            $footerHours = $viewData['hours'] ?? BusinessHour::query()
                ->orderBy('weekday')
                ->get();

            $view->with('footerHours', $footerHours);
        });
    }
}
