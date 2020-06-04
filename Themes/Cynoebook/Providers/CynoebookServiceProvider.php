<?php

namespace Themes\Cynoebook\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Base\Traits\AddsAsset;
use Modules\Base\Traits\LoadsConfigFile;
use Modules\Admin\Ui\Facades\TabManager;
use Themes\Cynoebook\Admin\CynoebookTabs;
use Themes\Cynoebook\Http\ViewComposers\LayoutComposer;
use Themes\Cynoebook\Http\ViewComposers\HomePageComposer;
use Themes\Cynoebook\Http\ViewComposers\EbooksFilterComposer;

class CynoebookServiceProvider extends ServiceProvider
{
    use LoadsConfigFile, AddsAsset;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('cynoebook', CynoebookTabs::class);

        View::composer('public.layout', LayoutComposer::class);
        View::composer('public.home.index', HomePageComposer::class);
        View::composer('public.ebooks.partials.filter', EbooksFilterComposer::class);

        $this->addAdminAssets('admin.cynoebook.settings.edit', [
            'admin.cynoebook.css', 'admin.files.css', 'admin.cynoebook.js', 'admin.files.js',
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadConfigs(['assets.php', 'permissions.php']);
    }
}
