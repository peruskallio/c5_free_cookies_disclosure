<?php     
defined('C5_EXECUTE') or die(_("Access Denied."));

class FreeCookiesDisclosurePackage extends Package {

	protected $pkgHandle = 'free_cookies_disclosure';
	protected $appVersionRequired = '5.5.1';
	protected $pkgVersion = '1.0.3';
	
	public function getPackageName() {
		return t("Free Cookies Disclosure");
	}
	
	public function getPackageDescription() {
		return t("Notify your users about EU Cookie Law.");
	}
	
	public function install() {
		$pkg = parent::install();
		Loader::model('single_page');
		$sp = SinglePage::add('/cookies_disclosure/', $pkg);
		$sp->moveToRoot();
		$sp = SinglePage::add('/dashboard/system/basics/cookies_disclosure/', $pkg);
		
		BlockType::installBlockTypeFromPackage('cookies_disclosure_form', $pkg);
		
		$pkg->saveConfig('COOKIES_DISCLOSURE_ALIGNMENT', 'top');
		$pkg->saveConfig('COOKIES_DISCLOSURE_COLOR_PROFILE', '');
		$pkg->saveConfig('COOKIES_DISCLOSURE_HIDE_INTERVAL', 10);
	}
	
	public function upgrade() {
		parent::upgrade();
	}
	
	public function on_start() {
		if (!defined('COOKIES_DISCLOSURE_DEBUG') && $this->config('COOKIES_DISCLOSURE_DEBUG') == 1) {
			define('COOKIES_DISCLOSURE_DEBUG', true);
		}
		if (!defined('COOKIES_DISCLOSURE_COLOR_PROFILE')) {
			define('COOKIES_DISCLOSURE_COLOR_PROFILE', $this->config('COOKIES_DISCLOSURE_COLOR_PROFILE'));
		}
		if (!defined('COOKIES_DISCLOSURE_ALIGNMENT')) {
			define('COOKIES_DISCLOSURE_ALIGNMENT', $this->config('COOKIES_DISCLOSURE_ALIGNMENT'));
		}
		if (!defined('COOKIES_DISCLOSURE_HIDE_INTERVAL')) {
			$int = intval($this->config('COOKIES_DISCLOSURE_HIDE_INTERVAL'));
			define('COOKIES_DISCLOSURE_HIDE_INTERVAL', $int > 0 ? $int : false);
		}
		if (!defined('COOKIES_DISCLOSURE_STACK_NAME_DEFAULT')) {
			define('COOKIES_DISCLOSURE_STACK_NAME_DEFAULT', 'Cookies Disclosure');
		}
		if (!defined('COOKIES_ALLOWED')) {
			$h = Loader::helper('cookie_allowance', 'free_cookies_disclosure');
			define('COOKIES_ALLOWED', !defined('COOKIES_DISCLOSURE_DEBUG') && $h->cookiesAllowed());
		}
		if (!defined('COOKIES_DISCLOSURE_PREVENT_TRACKING')) {
			define('COOKIES_DISCLOSURE_PREVENT_TRACKING', true);
		}

		// Render the disclosure notification together with the view
		Loader::library('disclosure_renderer', $this->pkgHandle);
		FreeCookiesDisclosureDisclosureRenderer::setEvents();
	}

}