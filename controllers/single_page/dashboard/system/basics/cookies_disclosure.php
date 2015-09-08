<?php
namespace Concrete\Package\FreeCookiesDisclosure\Controller\SinglePage\Dashboard\System\Basics;

use Core;
use Concrete\Core\Page\Page;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Controller\DashboardPageController;

defined('C5_EXECUTE') or die("Access Denied.");

class CookiesDisclosure extends DashboardPageController
{

    private $pkg;

    public function __construct()
    {
        $p = Page::getCurrentPage();
        parent::__construct($p);
        $this->pkg = Package::getByHandle('free_cookies_disclosure');
    }

    public function view()
    {

        $this->set('alignment', $this->pkg->getConfig()->get('cookies.disclosure_alignment'));

        $colorProfiles = array('' => t('Dark'), 'light' => t('Light'));
        $colorProfile = $this->pkg->getConfig()->get('cookies.disclosure_color_profile');
        if (!array_key_exists($colorProfile, $colorProfiles)) {
            $this->set('colorProfileCustom', $colorProfile);
            $colorProfile = 'custom';
        }
        $this->set('colorProfile', $colorProfile);
        $colorProfiles['custom'] = t('Custom');
        $this->set('colorProfiles', $colorProfiles);

        $hideInterval = $this->pkg->getConfig()->get('cookies.disclosure_hide_interval');
        $this->set('hideInterval', $hideInterval > 0 ? $hideInterval : '');
        $this->set('debug', $this->pkg->getConfig()->get('cookies.disclosure_debug') == 1);

        $this->set('hasMultilingual', is_object(Package::getByHandle('free_cookies_disclosure')));
        $this->set('form', Core::make('helper/form'));
    }

    public function save_settings()
    {
        $alignment = $this->post('alignment');
        $colorProfile = trim($this->post('color_profile'));
        if ($colorProfile == 'custom') {
            $colorProfile = trim($this->post('color_profile_custom'));
        }
        $hideInterval = intval($this->post('hide_interval'));

        $this->pkg->getConfig()->save('cookies.disclosure_alignment', $alignment);
        $this->pkg->getConfig()->save('cookies.disclosure_color_profile', $colorProfile);
        if ($hideInterval > 0) {
            $this->pkg->getConfig()->save('cookies.disclosure_hide_interval', $hideInterval);
        } else {
            if (strlen(trim($this->post('hide_interval'))) > 0) {
                $this->error->add(t('Hide interval must be greater than zero!'));
            }
            $this->pkg->getConfig()->clear('cookies.disclosure_hide_interval');
        }
        if ($this->post('debug')) {
            $this->pkg->getConfig()->save('cookies.disclosure_debug', 1);
        } else {
            $this->pkg->getConfig()->save('cookies.disclosure_debug', null);
        }

        if ($this->error->has()) {
            $this->view();
        } else {
            $this->redirect('/dashboard/system/basics/cookies_disclosure/saved_settings');
        }
    }

    public function saved_settings()
    {
        $this->set('message', t('Cookies disclosure settings saved.'));
        $this->view();
    }

}