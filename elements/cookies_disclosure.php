<?php
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Page\Stack\Stack;

defined('C5_EXECUTE') or die(_("Access Denied."));

$stack = Stack::getByName(Config::get('cookies.disclosure_stack_name'));
if (!is_object($stack)) {
    $stack = Stack::getByName($pkg->getConfig()->get('cookies.disclosure_stack_name_default'));
}

$cls = 'disclosure-' . $pkg->getConfig()->get('cookies.disclosure_alignment');
?>
<div id="ccm-cookiesDisclosure" class="<?php echo $cls ?>">
    <div class="disclosure-container">
        <?php if (is_object($stack)) : ?>
            <?php
            $sv = BlockType::getByHandle(BLOCK_HANDLE_STACK_PROXY);
            $sv->controller->set('stID', $stack->getCollectionID());
            $sv->render('view');
            ?>
        <?php else : ?>
            <div class="disclosure-content">
                <p><?php echo t("This site uses cookies. Some of the cookies we use are essential for parts of the site to operate and have already been set. You may delete and block all cookies from this site, but parts of the site will not work.") ?></p>
            </div>
            <?php
            $form = BlockType::getByHandle('cookies_disclosure_form');
            $form->controller->set('ajaxSubmit', 1);
            $form->controller->set('showCheckbox', 1);
            $form->controller->set('acceptText', t('I accept cookies from this site'));
            $form->controller->set('submitText', t('Allow Cookies'));
            $form->render('view');
            ?>
        <?php endif; ?>
        <div class="ccm-spacer">&nbsp;</div>
    </div>
</div>
