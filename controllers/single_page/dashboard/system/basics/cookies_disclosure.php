<?php
namespace Concrete\Package\FreeCookiesDisclosure\Controller\SinglePage\Dashboard\System\Basics;

use Core;
use Page;
use Package;
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
        $config = $this->pkg->getConfig();

        $this->set('alignment', $config->get('cookies.disclosure_alignment'));

        $colorProfiles = array('' => t('Dark'), 'light' => t('Light'));
        $colorProfile = $config->get('cookies.disclosure_color_profile');

        if (!array_key_exists($colorProfile, $colorProfiles)) {
            $this->set('colorProfileCustom', $colorProfile);
            $colorProfile = 'custom';
        }

        $this->set('colorProfile', $colorProfile);

        $colorProfiles['custom'] = t('Custom');
        $this->set('colorProfiles', $colorProfiles);

        $hideInterval = $config->get('cookies.disclosure_hide_interval');
        $this->set('hideInterval', $hideInterval > 0 ? $hideInterval : '');
        $this->set('debug', $config->get('cookies.disclosure_debug') == 1);
        $this->set('hasMultilingual', is_object(Package::getByHandle('free_cookies_disclosure')));
        $this->set('form', Core::make('helper/form'));
    }

    public function save_settings()
    {
        $config = $this->pkg->getConfig();

        $alignment = $this->post('alignment');
        $colorProfile = trim($this->post('color_profile'));

        if ($colorProfile == 'custom') {
            $colorProfile = trim($this->post('color_profile_custom'));
        }

        $config->save('cookies.disclosure_alignment', $alignment);
        $config->save('cookies.disclosure_color_profile', $colorProfile);

        $hideInterval = intval($this->post('hide_interval'));

        if ($hideInterval > 0) {
            $config->save('cookies.disclosure_hide_interval', $hideInterval);
        } else {
            if (strlen(trim($this->post('hide_interval'))) > 0) {
                $this->error->add(t('Hide interval must be greater than zero!'));
            }
            $config->clear('cookies.disclosure_hide_interval');
        }

        if ($this->post('debug')) {
            $config->save('cookies.disclosure_debug', 1);
        } else {
            $config->save('cookies.disclosure_debug', null);
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