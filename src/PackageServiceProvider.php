<?php
namespace Concrete\Package\FreeCookiesDisclosure\Src;

use Core;
use View;
use Page;
use Events;
use Package;
use Config;
use Loader;
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

    public function registerEvents()
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        // TODO refactor this code - it's taken from DisclosureRenderer class
        Events::addListener('on_page_view', function ($event) use ($pkg) {
            if (!defined('COOKIES_DISCLOSURE_STACK_NAME')) {
                if (is_object($ml = Package::getByHandle('multilingual'))) {

                    // TODO how do I load this with Core::make ?

                    Loader::model('section', 'multilingual');
                    $ms = MultilingualSection::getCurrentSection();
                    $lang = is_object($ms) ? $ms->getLanguage() : 'en';

                    define('COOKIES_DISCLOSURE_STACK_NAME', $pkg->getConfig()->get('cookies.DISCLOSURE_STACK_NAME_DEFAULT') . ' - ' . strtoupper($lang));
                } else {
                    define('COOKIES_DISCLOSURE_STACK_NAME', $pkg->getConfig()->get('cookies.DISCLOSURE_STACK_NAME_DEFAULT'));
                }
            }

            $p = Page::getCurrentPage();

            $v = View::getInstance();
            $v->addHeaderItem("\n" . '<script type="text/javascript">' . "\n" . 'var COOKIES_ALLOWED=' . ($pkg->getConfig()->get('cookies.ALLOWED') ? 'true' : 'false') . ";\n" . '</script>');

            if (!$p->isAdminArea() && !$p->isError() && !$pkg->getConfig()->get('cookies.ALLOWED')) {
                $html = Core::make('helper/html');
                $v->addHeaderItem($html->css('cookies_disclosure.css', 'free_cookies_disclosure'));
                $v->addHeaderItem('<!--[if lte IE 8]>' . $html->css('cookies_disclosure_ie.css',
                        'free_cookies_disclosure') . '<![endif]-->');
                if (strlen($pkg->getConfig()->get('cookies.DISCLOSURE_COLOR_PROFILE')) > 0) {
                    $v->addHeaderItem($html->css('cookies_disclosure_' . $pkg->getConfig()->get('cookies.DISCLOSURE_COLOR_PROFILE') . '.css',
                        'free_cookies_disclosure'));
                    $v->addHeaderItem('<!--[if lte IE 8]>' . $html->css('cookies_disclosure_' . $pkg->getConfig()->get('cookies.DISCLOSURE_COLOR_PROFILE') . '_ie.css',
                            'free_cookies_disclosure') . '<![endif]-->');
                }

                if (intval($pkg->getConfig()->get('cookies.DISCLOSURE_HIDE_INTERVAL')) > 0) {
                    // Add these to header so that this works on all of the single pages also
                    $v->addHeaderItem("\n" . '<script type="text/javascript">' . "\n" . 'var COOKIES_DISCLOSURE_HIDE_INTERVAL=' . intval($pkg->getConfig()->get('cookies.DISCLOSURE_HIDE_INTERVAL')) . ";\n" . '</script>');
                    $v->addHeaderItem($html->javascript('disclosure_hide.js', 'free_cookies_disclosure'));
                }

                // This needs to be loaded before the view is rendered
                // for the on_page_view methods to take effect!
                // e.g. $this->addFooterItem()
                ob_start();

                // TODO how to load cookies_disclosure without Loader ?

                Loader::packageElement('cookies_disclosure', 'free_cookies_disclosure');
                self::$_cookiesElement = ob_get_contents();
                ob_end_clean();
            }
        });

        Events::addListener('on_before_render', function ($event) {
            ob_start();
        });

        Events::addListener('on_render_complete', function ($event) use ($pkg) {
            $output = ob_get_contents();
            ob_end_clean();
            if ($pkg->getConfig()->get('cookies.DISCLOSURE_COLOR_PROFILE') && !$pkg->getConfig()->get('cookies.ALLOWED')) {
                // Cookies not yet allowed, so remove the
                // tracking codes from the page source.
                $trackingCode = Config::get('SITE_TRACKING_CODE');
                if (is_string($trackingCode) && strlen($trackingCode) > 0 && ($pos = strpos($output,
                        $trackingCode)) !== false
                ) {
                    $output = substr($output, 0, $pos) . substr($output, $pos + strlen($trackingCode));
                }
            }
            if ($pkg->getConfig()->get('cookies.COOKIES_DISCLOSURE_SOURCE_TOP')) {
                if (preg_match_all('/(.*)(<[ ]*body[^>]*>)(.*)/is', $output, $matches) > 0) {
                    echo $matches[1][0] . $matches[2][0] . PHP_EOL;
                    echo self::$_cookiesElement;
                    echo $matches[3][0];
                }
            } else {
                if (preg_match_all('/(.*)(<\/body>.*)/is', $output, $matches) > 0) {
                    echo $matches[1][0] . PHP_EOL;
                    echo self::$_cookiesElement;
                    echo $matches[2][0];
                }
            }
        });
    }
}