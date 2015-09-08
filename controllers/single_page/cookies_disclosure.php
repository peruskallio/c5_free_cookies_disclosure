<?php
namespace Concrete\Package\FreeCookiesDisclosure\Controller\SinglePage;

use Core;
use Concrete\Core\Page\Controller\PageController;

defined('C5_EXECUTE') or die("Access Denied.");

class CookiesDisclosure extends PageController
{

    public function view()
    {
        if ($this->isPost() && $this->post('allowCookies') == 1) {
            // Allow cookies for a year
            setcookie('cookies_allowed', 1, time() + 365 * 24 * 3600, '/');
            if ($this->post('ajax') == 1) {
                echo Core::make('helper/json')->encode(array('success' => 1));
                exit;
            } else {
                $this->redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            if ($this->post('ajax') == 1) {
                echo Core::make('helper/json')->encode(array('success' => 0));
                exit;
            } else {
                $this->redirect('/');
            }
        }
    }

}
