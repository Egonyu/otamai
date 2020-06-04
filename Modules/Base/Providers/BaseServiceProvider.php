<?php

namespace Modules\Base\Providers;

use Exception;
use Modules\Base\Helpers\Locale;
use Nwidart\Modules\Module;
use Modules\Setting\Entities\Setting;
use Illuminate\Support\ServiceProvider;
use Modules\Base\Traits\LoadsConfigFile;
use Modules\Setting\Repositories\SettingRepository;
use Illuminate\Database\Eloquent\Factory as DatabaseModelFactory;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class BaseServiceProvider extends ServiceProvider
{
    use LoadsConfigFile;

    /**
     * Base module specific middleware.
     *
     * @var array
     */
    protected $middleware = [
        'auth' => \Modules\Base\Http\Middleware\Authenticate::class,
        'admin' => \Modules\Base\Http\Middleware\AdminMiddleware::class,
        'guest' => \Modules\Base\Http\Middleware\VisitorMiddleware::class,
        'can' => \Modules\Base\Http\Middleware\Authorization::class,
        'localize' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        'locale_session_redirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
        'localization_redirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! config('app.installed')) {
            return;
        }

        $this->setupSupportedLocales();
        $this->registerSetting();
        $this->setupAppLocale();
        $this->hideDefaultLocaleInURL();
        $this->setupAppTimezone();
        $this->setupEmailConfig();
        $this->registerMiddleware();
        $this->registerInAdminState();
        $this->removeAdminRoutesOnFrontend();
        $this->registerModelDatabaseFactories();
        $this->registerModuleResourceNamespaces();
        
         
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
         $this->loadConfigs('config.php');
    }

    /**
     * Setup supported locales.
     *
     * @return void
     */
    private function setupSupportedLocales()
    {
        $supportedLocales = [];

        foreach ($this->getSupportedLocales() as $locale) {
            $supportedLocales[$locale]['name'] = Locale::name($locale);
        }

        $this->app['config']->set('laravellocalization.supportedLocales', $supportedLocales);
    }

    /**
     * Get supported locales from database.
     *
     * @return array
     */
    private function getSupportedLocales()
    {
        
        try {
            return Setting::get('supported_locales', [config('app.locale')]);
        } catch (Exception $e) {
            return [config('app.locale')];
        } 
    }

    /**
     * Hide default locale in url for non multi-locale mode.
     *
     * @return void
     */
    private function hideDefaultLocaleInURL()
    {
        
        if (! is_multilingual()) {
            $this->app['config']->set('laravellocalization.hideDefaultLocaleInURL', true);
        }
    }

    /**
     * Register setting binding.
     *
     * @return void
     */
    private function registerSetting()
    {
        $this->app->singleton('setting', function () {
            return new SettingRepository(Setting::getAllCached());
        });
    }

    /**
     * Setup application locale.
     *
     * @return string
     */
    private function setupAppLocale()
    {
        $defaultLocale = Setting::get('default_locale');
        $this->app['config']->set('app.locale', $defaultLocale);
        $this->app['config']->set('app.fallback_locale', $defaultLocale);

        $locale = is_null(LaravelLocalization::setLocale()) ? $defaultLocale : null;

        LaravelLocalization::setLocale($locale);
    }

    /**
     * Setup application timezone.
     *
     * @return void
     */
    private function setupAppTimezone()
    {
        $timezone = setting('default_timezone') ?? config('app.timezone');

        date_default_timezone_set($timezone);

        $this->app['config']->set('app.timezone', $timezone);
    }

    /**
     * Setup application mail config.
     *
     * @return void
     */
    private function setupEmailConfig()
    {
        $this->app['config']->set('mail.driver', 'smtp');
        $this->app['config']->set('mail.from.address', setting('email_from_address'));
        $this->app['config']->set('mail.from.name', setting('email_from_name'));
        $this->app['config']->set('mail.host', setting('email_host'));
        $this->app['config']->set('mail.port', setting('email_port'));
        $this->app['config']->set('mail.username', setting('email_username'));
        $this->app['config']->set('mail.password', setting('email_password'));
        $this->app['config']->set('mail.encryption', setting('email_encryption'));
    }

    /**
     * Register the filters.
     *
     * @return void
     */
    private function registerMiddleware()
    {
        foreach ($this->middleware as $name => $middleware) {
            $this->app['router']->aliasMiddleware($name, $middleware);
        }
    }

    /**
     * Register inAdmin state to the IoC container.
     *
     * @return void
     */
    private function registerInAdminState()
    {
        if ($this->app->runningInConsole()) {
            return $this->app['inAdmin'] = false;
        }

        $index = in_array($this->app['request']->segment(1), setting('supported_locales'))
            ? 2
            : 1;

        $this->app['inAdmin'] = $this->app['request']->segment($index) === 'admin';
    }


    /**
     * Register model factories of all modules.
     *
     * @return void
     */
    private function registerModelDatabaseFactories()
    {
        foreach ($this->app['modules']->allEnabled() as $module) {
            $dir = "{$module->getPath()}/Database/Factories";

            if (is_dir($dir)) {
                $this->app[DatabaseModelFactory::class]->load($dir);
            }
        }
    }
    
    /**
     * Remove admin routes on frontend for ziggy package.
     *
     * @return void
     */
    private function removeAdminRoutesOnFrontend()
    {
        if (! $this->app['inAdmin']) {
            $this->app['config']->set('ziggy.blacklist', ['admin.*']);
        }
    }

    /**
     * Register the modules aliases.
     *
     * @return void
     */
    private function registerModuleResourceNamespaces()
    {
        foreach ($this->app['modules']->allEnabled() as $module) {
            $this->registerViewNamespace($module);
            $this->registerLanguageNamespace($module);
        }
    }

    /**
     * Register the view namespaces for the modules.
     *
     * @param \Nwidart\Modules\Module $module
     */
    private function registerViewNamespace(Module $module)
    {
        $this->loadViewsFrom("{$module->getPath()}/Resources/views", $module->getLowerName());
    }

    /**
     * Register the language namespaces for the modules.
     *
     * @param \Nwidart\Modules\Module $module
     */
    private function registerLanguageNamespace(Module $module)
    {
        $langDir = base_path("resources/lang/vendor/{$module->getLowerName()}");

        if (is_dir($langDir)) {
            $this->loadTranslationsFrom($langDir, $module->getLowerName());
        }

        $this->loadTranslationsFrom("{$module->getPath()}/Resources/lang", $module->getLowerName());
    }
}
