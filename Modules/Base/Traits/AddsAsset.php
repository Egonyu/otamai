<?php

namespace Modules\Base\Traits;

use Modules\Base\Events\FetchingAssets;

trait AddsAsset
{
    public function addAdminAssets($routes, array $assets)
    {
        if (config('app.installed') && $this->app['inAdmin']) {
            $this->addAssets($routes, $assets);
        }
    }

    public function addAssets($routes, array $assets)
    {
        $this->app['events']->listen(FetchingAssets::class, function (FetchingAssets $event) use ($routes, $assets) {
            if ($event->checkRoutes(array_wrap($routes))) {
                $event->requireAssets($assets);
            }
        });
    }
}
