<?php 
defined('C5_EXECUTE') or die("Access Denied.");

class DashboardSystemBasicsCookiesDisclosureController extends DashboardBaseController {
	
	private $_pkg;
	
	public function __construct() {
		parent::__construct();
		$this->_pkg = Package::getByHandle('free_cookies_disclosure');
	}
	
	public function view() {
		$this->set('alignment', $this->_pkg->config('COOKIES_DISCLOSURE_ALIGNMENT'));
		
		$colorProfiles = array('' => t('Dark'), 'light' => t('Light'));
		$colorProfile = $this->_pkg->config('COOKIES_DISCLOSURE_COLOR_PROFILE');
		if (!array_key_exists($colorProfile, $colorProfiles)) {
			$this->set('colorProfileCustom', $colorProfile);
			$colorProfile = 'custom';
		}
		$this->set('colorProfile', $colorProfile);
		$colorProfiles['custom'] = t('Custom');
		$this->set('colorProfiles', $colorProfiles);
		
		$hideInterval = $this->_pkg->config('COOKIES_DISCLOSURE_HIDE_INTERVAL');
		$this->set('hideInterval', $hideInterval > 0 ? $hideInterval : '');
		$this->set('debug', $this->_pkg->config('COOKIES_DISCLOSURE_DEBUG') == 1);
		
		$this->set('hasMultilingual', is_object(Package::getByHandle('free_cookies_disclosure')));
	}
	
	public function save_settings() {
		$alignment = $this->post('alignment');
		$colorProfile = trim($this->post('color_profile'));
		if ($colorProfile == 'custom') {
			$colorProfile = trim($this->post('color_profile_custom'));
		}
		$hideInterval = intval($this->post('hide_interval'));
		
		$this->_pkg->saveConfig('COOKIES_DISCLOSURE_ALIGNMENT', $alignment);
		$this->_pkg->saveConfig('COOKIES_DISCLOSURE_COLOR_PROFILE', $colorProfile);
		if ($hideInterval > 0) {
			$this->_pkg->saveConfig('COOKIES_DISCLOSURE_HIDE_INTERVAL', $hideInterval);
		} else {
			if (strlen(trim($this->post('hide_interval'))) > 0) {
				$this->error->add(t('Hide interval must be greater than zero!'));
			}
			$this->_pkg->clearConfig('COOKIES_DISCLOSURE_HIDE_INTERVAL');
		}
		if ($this->post('debug')) {
			$this->_pkg->saveConfig('COOKIES_DISCLOSURE_DEBUG', 1);
		} else {
			$this->_pkg->clearConfig('COOKIES_DISCLOSURE_DEBUG');
		}
		
		if ($this->error->has()) {
			$this->view();
		} else {
			$this->redirect('/dashboard/system/basics/cookies_disclosure/saved_settings');
		}
	}
	
	public function saved_settings() {
		$this->set('message', t('Cookies disclosure settings saved.'));
		$this->view();
	}
	
}