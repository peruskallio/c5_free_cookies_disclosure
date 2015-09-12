<?php
namespace Concrete\Package\FreeCookiesDisclosure\Src;

defined('C5_EXECUTE') or die("Access Denied.");

use Route;

class PackageRouteProvider
{

    public static function registerRoutes()
    {
        Route::register('/ccm/free_cookies_disclosure/set_cookie',
            '\Concrete\Package\FreeCookiesDisclosure\Controller\Frontend\CookiesDisclosure::set_cookie');
    }

}