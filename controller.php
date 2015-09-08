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

        $pkg->getConfig()->save('cookies.disclosure_alignment', 'top');
        $pkg->getConfig()->save('cookies.disclosure_color_profile', '');
        $pkg->getConfig()->save('cookies.disclosure_hide_interval', 10);
        $pkg->getConfig()->save('cookies.disclosure_debug', false);
        $pkg->getConfig()->save('cookies.disclosure_stack_name_default', 'Cookies Disclosure');
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

        if (!$this->getConfig()->has('cookies.disclosure_hide_interval')) {
            $this->getConfig()->set('cookies.disclosure_hide_interval', false);
        }

        if (!$this->getConfig()->has('cookies.disclosure_stack_name_default')) {
            $this->getConfig()->set('cookies.disclosure_stack_name_default', 'Cookies Disclosure');
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
    }

}