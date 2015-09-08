<?php
namespace Concrete\Package\FreeCookiesDisclosure\Src;

use View;
use Page;
use Events;
use Config;
use Concrete\Core\Package\Package;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Foundation\Service\Provider as ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{

    private $cookiesElement = '';
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

        $color_profile = $pkg->getConfig()->get('cookies.disclosure_color_profile');
        if (is_string($color_profile) && strlen($color_profile) > 0) {
            $al->register('css', 'free_cookies_disclosure/cookies_disclosure_' . $color_profile,
                'css/cookies_disclosure_' . $color_profile . '.css',
                array('minify' => true, 'combine' => true),
                $this->pkgHandle);
        }

        $v->requireAsset('javascript', 'jquery');
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

            $asset = new \Concrete\Core\Asset\JavascriptInlineAsset();
            $asset->setAssetURL('var COOKIES_ALLOWED=' . ($pkg->getConfig()->get('cookies.allowed') ? 'true' : 'false') . ";");

            if (!$p->isAdminArea() && !$p->isError() && !$pkg->getConfig()->get('cookies.allowed')) {

                $v->requireAsset('css', 'free_cookies_disclosure/cookies_disclosure');

                $color_profile = $pkg->getConfig()->get('cookies.disclosure_color_profile');
                if (is_string($color_profile) && strlen($color_profile) > 0) {
                    $v->requireAsset('css', 'free_cookies_disclosure/cookies_disclosure_' . $color_profile);
                }

                if (intval($pkg->getConfig()->get('cookies.disclosure_hide_interval')) > 0) {
                    $v->addHeaderItem("\n" . '<script type="text/javascript">' . "\n" . 'var COOKIES_DISCLOSURE_HIDE_INTERVAL=' . intval($pkg->getConfig()->get('cookies.disclosure_hide_interval')) . ";\n" . '</script>');
                    $v->addHeaderItem("\n" . '<script type="text/javascript">' . "\n" . 'var ccmi18n_cookiesdisclosure = { allowCookies: "' . t("You need to allow cookies for this site!") . '" }' . ";\n" . '</script>');
                    $v->requireAsset('javascript', 'free_cookies_disclosure/disclosure_hide');
                }

                // This needs to be loaded before the view is rendered
                ob_start();
                View::element('cookies_disclosure', array('pkg' => $pkg), $this->pkgHandle);
                $this->cookiesElement = ob_get_contents();
                ob_end_clean();
            }
        });

        Events::addListener('on_page_output', function ($event) use ($pkg) {

            $output = $event->getArgument('contents');

            if ($pkg->getConfig()->get('cookies.disclosure_color_profile') && !$pkg->getConfig()->get('cookies.allowed')) {
                // Cookies not yet allowed, so remove the
                // tracking codes from the page source.
                $trackingCode = Config::get('SITE_TRACKING_CODE');
                if (is_string($trackingCode) && strlen($trackingCode) > 0 && ($pos = strpos($output,
                        $trackingCode)) !== false
                ) {
                    $output = substr($output, 0, $pos) . substr($output, $pos + strlen($trackingCode));
                }
            }

            if (preg_match_all('/(.*)(<\/body>.*)/is', $output, $matches) > 0) {
                $output = $matches[1][0] . PHP_EOL . $this->cookiesElement . $matches[2][0];
            }

            $p = Page::getCurrentPage();

            if (!$p->isAdminArea()) {
                $event->setArgument('contents', $output);
            }
        });
    }

}