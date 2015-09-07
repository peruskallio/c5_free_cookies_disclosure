<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
class FreeCookiesDisclosureDisclosureRenderer {
	
	private static $_cookiesElement = '';
	
	public static function setEvents() {
		Events::extend('on_page_view', 'FreeCookiesDisclosureDisclosureRenderer', 'on_page_view', __FILE__);
	}
	
	public static function on_page_view() {
		if (!defined('COOKIES_DISCLOSURE_STACK_NAME')) {
			if (is_object($ml = Package::getByHandle('multilingual'))) {
				Loader::model('section', 'multilingual');
				$ms = MultilingualSection::getCurrentSection();
				$lang = is_object($ms) ? $ms->getLanguage() : 'en';
				define('COOKIES_DISCLOSURE_STACK_NAME', COOKIES_DISCLOSURE_STACK_NAME_DEFAULT . ' - ' . strtoupper($lang));
			} else {
				define('COOKIES_DISCLOSURE_STACK_NAME', COOKIES_DISCLOSURE_STACK_NAME_DEFAULT);
			}
		}
		
		$p = Page::getCurrentPage();
		
		$v = View::getInstance();
		$v->addHeaderItem("\n".'<script type="text/javascript">'."\n".'var COOKIES_ALLOWED='.(COOKIES_ALLOWED ? 'true' : 'false').";\n".'</script>');
		
		if (!$p->isAdminArea() && !$p->isError() && !COOKIES_ALLOWED) {
			$html = Loader::helper('html');
			$v->addHeaderItem($html->css('cookies_disclosure.css', 'free_cookies_disclosure'));
			$v->addHeaderItem('<!--[if lte IE 8]>' . $html->css('cookies_disclosure_ie.css', 'free_cookies_disclosure') . '<![endif]-->');
			if (strlen(COOKIES_DISCLOSURE_COLOR_PROFILE) > 0) {
				$v->addHeaderItem($html->css('cookies_disclosure_' . COOKIES_DISCLOSURE_COLOR_PROFILE . '.css', 'free_cookies_disclosure'));
				$v->addHeaderItem('<!--[if lte IE 8]>' . $html->css('cookies_disclosure_' . COOKIES_DISCLOSURE_COLOR_PROFILE . '_ie.css', 'free_cookies_disclosure') . '<![endif]-->');
			}
			
			if (intval(COOKIES_DISCLOSURE_HIDE_INTERVAL) > 0) {
				// Add these to header so that this works on all of the single pages also
				$v->addHeaderItem("\n".'<script type="text/javascript">'."\n".'var COOKIES_DISCLOSURE_HIDE_INTERVAL='.intval(COOKIES_DISCLOSURE_HIDE_INTERVAL).";\n".'</script>');
				$v->addHeaderItem($html->javascript('disclosure_hide.js', 'free_cookies_disclosure'));
			}
			
			// This needs to be loaded before the view is rendered 
			// for the on_page_view methods to take effect!
			// e.g. $this->addFooterItem()
			ob_start();
			Loader::packageElement('cookies_disclosure', 'free_cookies_disclosure');
			self::$_cookiesElement = ob_get_contents();
			ob_end_clean();
			
			// Noticed strange behavior with on_page_output event in some cases.
			// That does not work 100% of the cases.
			$file = __FILE__;
			Events::extend('on_before_render', 'FreeCookiesDisclosureDisclosureRenderer', 'on_before_render', $file);
			Events::extend('on_render_complete', 'FreeCookiesDisclosureDisclosureRenderer', 'on_render_complete', $file);
		}
	}
	
	public static function on_before_render() {
		ob_start();
	}
	
	public static function on_render_complete() {
		$output = ob_get_contents();
		ob_end_clean();
		if (COOKIES_DISCLOSURE_PREVENT_TRACKING && !COOKIES_ALLOWED) {
			// Cookies not yet allowed, so remove the 
			// tracking codes from the page source.
			$trackingCode = Config::get('SITE_TRACKING_CODE');
			if (is_string($trackingCode) && strlen($trackingCode) > 0 && ($pos = strpos($output, $trackingCode)) !== false) {
				$output = substr($output, 0, $pos) . substr($output, $pos+strlen($trackingCode));
			}
		}
		if (defined('COOKIES_DISCLOSURE_SOURCE_TOP') && COOKIES_DISCLOSURE_SOURCE_TOP) {
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
	}
	
}
