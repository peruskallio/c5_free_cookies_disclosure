<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

class FreeCookiesDisclosureCookieAllowanceHelper {
	
	public function cookiesAllowed() {
		if (!isset($_SESSION['cookies_allowed'])) {
			if (isset($_COOKIE['cookies_allowed']) && $_COOKIE['cookies_allowed'] == true) {
				$_SESSION['cookies_allowed'] = true;
				return true;
			}
			return false;
		}
		return $_SESSION['cookies_allowed'] == true;
	}
	
}
