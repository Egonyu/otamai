<?php

namespace Modules\Admin\Providers;

use Illuminate\Support\Facades\View;
use Modules\Base\Traits\AddsAsset;
use Illuminate\Support\ServiceProvider;
use Modules\Base\Traits\LoadsConfigFile;
use Modules\Admin\Http\ViewComposers\AssetsComposer;

class AdminServiceProvider extends ServiceProvider
{
    use AddsAsset, LoadsConfigFile;
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('admin::layout', AssetsComposer::class);
        View::composer('admin::fullwidth-layout', AssetsComposer::class);
        

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadConfigs(['assets.php', 'permissions.php']);
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

}
