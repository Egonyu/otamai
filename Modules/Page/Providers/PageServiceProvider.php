<?php

namespace Modules\Page\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Page\Admin\Tabs\PageTabs;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Base\Traits\LoadsConfigFile;


class PageServiceProvider extends ServiceProvider
{
    use LoadsConfigFile;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('pages', PageTabs::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadConfigs('permissions.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
