<?php
namespace Concrete\Package\FreeCookiesDisclosure\Src;

use Concrete\Core\Asset\JavascriptInlineAsset;
use View;
use Page;
use Events;
use Config;
use Package;
use AssetList;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Foundation\Service\Provider as ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{

    protected $pkgHandle = 'free_cookies_disclosure';

    public function register()
    {
        $singletons = array(
            'allowance' => '\Concrete\Package\FreeCookiesDisclosure\Src\CookieAllowance',
        );

        foreach ($singletons as $key => $value) {
            $this->app->singleton($this->pkgHandle . '/' . $key, $value);
        }
    }

    public function registerAssets()
    {
        $v = View::getInstance();
        $al = AssetList::getInstance();
        $pkg = Package::getByHandle($this->pkgHandle);

        $al->register('javascript', 'free_cookies_disclosure/disclosure_ajax_form', 'js/disclosure_ajax_form.js',
            array('minify' => true, 'combine' => true),
            $this->pkgHandle);

        $al->register('javascript', 'free_cookies_disclosure/disclosure_hide', 'js/disclosure_hide.js',
            array('minify' => true, 'combine' => true),
            $this->pkgHandle);

        $al->register('css', 'free_cookies_disclosure/cookies_disclosure', 'css/cookies_disclosure.css',
            array('minify' => true, 'combine' => true),
            $this->pkgHandle);

        $colorProfile = $pkg->getConfig()->get('cookies.disclosure_color_profile');
        if (is_string($colorProfile) && strlen($colorProfile) > 0) {
            $al->register('css', 'free_cookies_disclosure/cookies_disclosure_' . $colorProfile,
                'css/cookies_disclosure_' . $colorProfile . '.css',
                array('minify' => true, 'combine' => true),
                $this->pkgHandle);
        }
    }

    public function registerEvents()
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        Events::addListener('on_page_view', function ($event) use ($pkg) {

            if (!Config::get('cookies.disclosure_stack_name')) {
                $ms = Section::getCurrentSection();
                $lang = is_object($ms) ? $ms->getLanguage() : 'en';

                Config::set('cookies.disclosure_stack_name',
                    $pkg->getConfig()->get('cookies.disclosure_stack_name_default') . ' - ' . strtoupper($lang));
            }

            $p = Page::getCurrentPage();
            $v = View::getInstance();

            $asset = new JavascriptInlineAsset();
            $asset->setAssetURL('var COOKIES_ALLOWED=' . ($pkg->getConfig()->get('cookies.allowed') ? 'true' : 'false') . ";");
            $v->addHeaderItem($asset);

            if (!$p->isAdminArea() && !$p->isError() && !$pkg->getConfig()->get('cookies.allowed')) {

                $v->requireAsset('javascript', 'jquery');
                $v->requireAsset('css', 'free_cookies_disclosure/cookies_disclosure');

                $colorProfile = $pkg->getConfig()->get('cookies.disclosure_color_profile');
                if (is_string($colorProfile) && strlen($colorProfile) > 0) {
                    $v->requireAsset('css', 'free_cookies_disclosure/cookies_disclosure_' . $colorProfile);
                }

                if (intval($pkg->getConfig()->get('cookies.disclosure_hide_interval')) > 0) {

                    $asset = new JavascriptInlineAsset();
                    $asset->setAssetURL('var COOKIES_DISCLOSURE_HIDE_INTERVAL=' . intval($pkg->getConfig()->get('cookies.disclosure_hide_interval')) . ";\n"
                        . 'var ccmi18n_cookiesdisclosure = { allowCookies: "' . t("You need to allow cookies for this site!") . '" }' . ";");
                    $v->addHeaderItem($asset);

                    $v->requireAsset('javascript', 'free_cookies_disclosure/disclosure_hide');
                    $v->requireAsset('javascript', 'free_cookies_disclosure/disclosure_ajax_form');
                }
            }
        });

        Events::addListener('on_page_output', function ($event) use ($pkg) {

            $p = Page::getCurrentPage();

            if (!$p->isAdminArea() && !$p->isError() && !$pkg->getConfig()->get('cookies.allowed')) {

                $output = $event->getArgument('contents');

                $view = new View('frontend/cookies_disclosure');
                $view->setPackageHandle('free_cookies_disclosure');
                $cookiesElement = $view->render();

                if ($pkg->getConfig()->get('cookies.disclosure_color_profile') && !$pkg->getConfig()->get('cookies.allowed')) {
                    // Cookies not yet allowed, so remove the
                    // tracking codes from the page source.
                    $trackingCode = Config::get('concrete.seo.tracking.code');

                    if (is_string($trackingCode) && strlen($trackingCode) > 0 && ($pos = strpos($output,
                            $trackingCode)) !== false
                    ) {
                        $output = substr($output, 0, $pos) . substr($output, $pos + strlen($trackingCode));
                    }
                }

                if (preg_match_all('/(.*)(<\/body>.*)/is', $output, $matches) > 0) {
                    $output = $matches[1][0] . PHP_EOL . $cookiesElement . $matches[2][0];
                }

                $event->setArgument('contents', $output);
            }
        });
    }

}