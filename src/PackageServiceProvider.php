<?php
namespace Concrete\Package\FreeCookiesDisclosure\Src;

use Core;
use View;
use Page;
use Events;
use Config;
use Package;
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

    public function registerEvents()
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        Events::addListener('on_page_view', function ($event) use ($pkg) {

            if (!defined('COOKIES_DISCLOSURE_STACK_NAME')) {
                $ms = Section::getCurrentSection();
                $lang = is_object($ms) ? $ms->getLanguage() : 'en';

                define('COOKIES_DISCLOSURE_STACK_NAME', $pkg->getConfig()->get('cookies.disclosure_stack_name_default') . ' - ' . strtoupper($lang));
            }

            $asset = new \Concrete\Core\Asset\JavascriptInlineAsset();
            $p = Page::getCurrentPage();
            $v = View::getInstance();

            $asset->setAssetURL('var COOKIES_ALLOWED=' . ($pkg->getConfig()->get('cookies.allowed') ? 'true' : 'false') . ";");

            if (!$p->isAdminArea() && !$p->isError() && !$pkg->getConfig()->get('cookies.allowed')) {
                $html = Core::make('helper/html');
                $v->addHeaderItem($html->css('cookies_disclosure.css', $this->pkgHandle));
                $v->addHeaderItem('<!--[if lte IE 8]>' . $html->css('cookies_disclosure_ie.css',
                        $this->pkgHandle) . '<![endif]-->');
                if (strlen($pkg->getConfig()->get('cookies.disclosure_color_profile')) > 0) {
                    $v->addHeaderItem($html->css('cookies_disclosure_' . $pkg->getConfig()->get('cookies.disclosure_color_profile') . '.css',
                        $this->pkgHandle));
                    $v->addHeaderItem('<!--[if lte IE 8]>' . $html->css('cookies_disclosure_' . $pkg->getConfig()->get('cookies.disclosure_color_profile') . '_ie.css',
                            $this->pkgHandle) . '<![endif]-->');
                }

                if (intval($pkg->getConfig()->get('cookies.disclosure_hide_interval')) > 0) {
                    // Add these to header so that this works on all of the single pages also
                    $v->addFooterItem("\n" . '<script type="text/javascript">' . "\n" . 'var COOKIES_DISCLOSURE_HIDE_INTERVAL=' . intval($pkg->getConfig()->get('cookies.disclosure_hide_interval')) . ";\n" . '</script>');
                    $v->addFooterItem("\n" . '<script type="text/javascript">' . "\n" . 'var ccmi18n_cookiesdisclosure = { allowCookies: "' . t("You need to allow cookies for this site!") . '" }' . ";\n" . '</script>');
                    $v->addFooterItem($html->javascript('disclosure_hide.js', $this->pkgHandle));
                }

                // This needs to be loaded before the view is rendered
                // for the on_page_view methods to take effect!
                // e.g. $this->addFooterItem()
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