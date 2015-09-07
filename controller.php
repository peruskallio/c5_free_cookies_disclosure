<?php
namespace Concrete\Package\FreeCookiesDisclosure;

defined('C5_EXECUTE') or die(_("Access Denied."));

use Core;
use SinglePage;
use Concrete\Core\Package\Package;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Package\FreeCookiesDisclosure\Src\PackageServiceProvider;

class Controller extends Package
{

    protected $pkgHandle = 'free_cookies_disclosure';
    protected $appVersionRequired = '5.7.4';
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
        $pkg = parent::install();

        $sp = SinglePage::add('/cookies_disclosure/', $pkg);
        $sp->moveToRoot();

        SinglePage::add('/dashboard/system/basics/cookies_disclosure/', $pkg);

        $bt = BlockType::getByHandle('cookies_disclosure_form');
        if (!is_object($bt)) {
            BlockType::installBlockType('cookies_disclosure_form', $pkg);
        }

        $pkg->getConfig()->save('cookies.DISCLOSURE_ALIGNMENT', 'top');
        $pkg->getConfig()->save('cookies.DISCLOSURE_COLOR_PROFILE', '');
        $pkg->getConfig()->save('cookies.DISCLOSURE_HIDE_INTERVAL', 10);
        $pkg->getConfig()->save('cookies.DISCLOSURE_DEBUG', false);
        $pkg->getConfig()->save('cookies.DISCLOSURE_STACK_NAME_DEFAULT', 'Cookies Disclosure');
    }

    public function upgrade()
    {
        parent::upgrade();
    }

    public function on_start()
    {
        $app = Core::getFacadeApplication();
        $sp = new PackageServiceProvider($app);
        $sp->register();
        $sp->registerEvents();

        if (!$this->getConfig()->has('cookies.DISCLOSURE_HIDE_INTERVAL')) {
            $this->getConfig()->set('cookies.DISCLOSURE_HIDE_INTERVAL', false);
        }

        if (!$this->getConfig()->has('cookies.DISCLOSURE_STACK_NAME_DEFAULT')) {
            $this->getConfig()->set('cookies.DISCLOSURE_STACK_NAME_DEFAULT', 'Cookies Disclosure');
        }

        if (!$this->getConfig()->has('cookies.ALLOWED')) {
            $h = Core::make('free_cookies_disclosure/allowance');
            $this->getConfig()->set('cookies.ALLOWED',
                !$this->getConfig()->get('cookies.DISCLOSURE_DEBUG') && $h->cookiesAllowed());
        }

        if (!$this->getConfig()->has('cookies.DISCLOSURE_PREVENT_TRACKING')) {
            $this->getConfig()->set('cookies.DISCLOSURE_PREVENT_TRACKING', true);
        }

    }

}