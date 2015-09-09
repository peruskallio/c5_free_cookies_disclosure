<?php
namespace Concrete\Package\FreeCookiesDisclosure\Src;

use Core;
use Request;

defined('C5_EXECUTE') or die(_("Access Denied."));

class CookieAllowance
{

    public function cookiesAllowed()
    {
        $r = Request::getInstance();
        $session = $r->getSession();

        if (!$session) {
            $session = Core::make('session');
        }

        if (!$session->has('cookies_allowed')) {
            $cookie = Core::make('cookie');

            if ($cookie->has('cookies_allowed') && $cookie->get('cookies_allowed') == true) {
                $session->set('cookies_allowed', true);

                return true;
            }

            return false;
        }

        return $session->get('cookies_allowed') == true;
    }

}
