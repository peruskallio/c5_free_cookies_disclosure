<?php
namespace Concrete\Package\FreeCookiesDisclosure\Block\CookiesDisclosureForm;

use Core;
use Loader;
use Concrete\Core\Block\BlockController;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends BlockController
{

    protected $btTable = 'btCookiesDisclosureForm';

    protected $btInterfaceWidth = "550";
    protected $btInterfaceHeight = "450";

    public function getBlockTypeDescription()
    {
        return t("User action form for cookies disclosure.");
    }

    public function getBlockTypeName()
    {
        return t("Cookies Disclosure Form");
    }

    public function add()
    {
        $this->set('showCheckbox', 1);
        $this->set('ajaxSubmit', 1);
        $this->set('acceptText', t('I accept cookies from this site'));
        $this->set('submitText', t('Allow Cookies'));
    }

    public function view()
    {
        if ($this->ajaxSubmit || $this->get('ajaxSubmit')) {
//            $html = Core::make('helper/html');
            // this didn't work - got blank page instead of JS - moved to PackageServiceProvider#registerEvents()#on_page_view
            //$this->addFooterItem($html->javascript(Core::make('helper/concrete/urls')->getToolsUrl('disclosure_i18n_js', 'free_cookies_disclosure')));
//            $this->addFooterItem($html->javascript('disclosure_ajax_form.js', 'free_cookies_disclosure'));
        }
    }

    public function save($data)
    {
        $data['showCheckbox'] = isset($data['showCheckbox']) ? $data['showCheckbox'] : 0;
        $data['ajaxSubmit'] = isset($data['ajaxSubmit']) ? $data['ajaxSubmit'] : 0;
        $data['acceptText'] = trim($data['acceptText']);
        $data['submitText'] = trim($data['submitText']);
        parent::save($data);
    }

    public function registerViewAssets()
    {
        $this->requireAsset('javascript', 'free_cookies_disclosure/disclosure_ajax_form');
    }

}