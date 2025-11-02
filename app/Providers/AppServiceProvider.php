<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Make Laravel treat /public_html as the public path
        $this->app->bind('path.public', function () {
            return base_path(); // => /home/.../public_html
        });
    }
}
