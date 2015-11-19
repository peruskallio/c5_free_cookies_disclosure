<?php
namespace Concrete\Package\FreeCookiesDisclosure;

defined('C5_EXECUTE') or die(_("Access Denied."));

use Concrete\Package\FreeCookiesDisclosure\Src\PackageRouteProvider;
use Core;
use Package;
use BlockType;
use Concrete\Package\FreeCookiesDisclosure\Src\PackageServiceProvider;
use Mainio\C5\Twig\TwigServiceProvider;
use Mainio\C5\Twig\Page\Single as SinglePage;

class Controller extends Package
{

    protected $pkgHandle = 'free_cookies_disclosure';
    protected $appVersionRequired = '5.7.5';
    protected $pkgVersion = '2.0.0rc1';

    public function getPackageName()
    {
        return t("Free Cookies Disclosure");
    }

    public function getPackageDescription()
    {
        return t("Notify your users about EU Cookie Law.");
    }

    public function install()
    {
        if (version_compare(phpversion(), '5.4', '<')) {
            throw new \Exception(t("Minimum PHP version required by this package is 5.4 as described in our documentation. Please update your PHP to a newer version."));
        }

        $pkg = parent::install();

        $this->loadDependencies();
        $this->clearTwigCache($pkg);

        SinglePage::add('/dashboard/system/basics/cookies_disclosure/', $pkg);

        $bt = BlockType::getByHandle('cookies_disclosure_form');
        if (!is_object($bt)) {
            BlockType::installBlockType('cookies_disclosure_form', $pkg);
        }

        $pkg->getConfig()->save('cookies.disclosure_alignment', 'top');
        $pkg->getConfig()->save('cookies.disclosure_color_profile', '');
        $pkg->getConfig()->save('cookies.disclosure_hide_interval', 10);
        $pkg->getConfig()->save('cookies.disclosure_debug', false);
        $pkg->getConfig()->save('cookies.disclosure_stack_name_default', 'Cookies Disclosure');
    }

    public function upgrade()
    {
        parent::upgrade();

        $this->clearTwigCache($this);
    }

    public function on_start()
    {
        $this->loadDependencies();

        $app = Core::getFacadeApplication();
        $sp = new PackageServiceProvider($app);
        $sp->register();
        $sp->registerAssets();

        // Register the twig services for the single pages and CLI
        $this->registerTwigServices($this);

        if (!$this->getConfig()->has('cookies.disclosure_hide_interval')) {
            $this->getConfig()->set('cookies.disclosure_hide_interval', false);
        }

        if (!$this->getConfig()->has('cookies.allowed')) {
            $h = Core::make('free_cookies_disclosure/allowance');
            $this->getConfig()->set('cookies.allowed',
                !$this->getConfig()->get('cookies.disclosure_debug') && $h->cookiesAllowed());
        }

        if (!$this->getConfig()->has('cookies.disclosure_prevent_tracking')) {
            $this->getConfig()->set('cookies.disclosure_prevent_tracking', true);
        }

        $sp->registerEvents();

        PackageRouteProvider::registerRoutes();
    }

    protected function loadDependencies()
    {
        // No other way of managing the composer dependencies currently.
        // See: https://github.com/concrete5/concrete5-5.7.0/issues/360
        $filesystem = new \Illuminate\Filesystem\Filesystem();
        $loader = $filesystem->getRequire(dirname(__FILE__) . '/vendor/autoload.php');

        $this->intlFix($loader);
    }

    protected function intlFix(\Composer\Autoload\ClassLoader $loader)
    {
        // When defining the load path for the 'Collator' class, it messes up
        // punic as punic expects PHP's intl to be installed when this class
        // exists in the global namespace. The symfony-intl's Collator only
        // works with the 'en' locale which becomes a problem e.g. with the
        // c5's default locale (en_US). The 'Collator' class isn't used
        // anywhere in this add-on, so it is not needed.
        $loader->addClassMap(array('Collator' => null));
    }

    protected function clearTwigCache(Package $pkg)
    {
        $this->registerTwigServices($pkg);
        Core::make('free_cookies_disclosure/twig')->clearCacheDirectory();
    }

    protected function registerTwigServices(Package $pkg)
    {
        $spt = new TwigServiceProvider(Core::getFacadeApplication(), $pkg);
        $spt->register();
    }

}