<?php defined('C5_EXECUTE') or die("Access Denied.");

$pkg = Package::getByHandle($this->pkgHandle);

$stackName = Config::get('cookies.disclosure_stack_name', 'Cookies Disclosure');
$stack = Stack::getByName($stackName);

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
                <p><?php echo t("This site uses cookies. By continuing to use this website, you accept our use of cookies.") ?></p>
            </div>
            <?php
                $form = BlockType::getByHandle('cookies_disclosure_form');
                $form->controller->set('ajaxSubmit', 1);
                $form->controller->set('submitText', t("Got it!"));
                $form->render('view');
            ?>
        <?php endif; ?>
    </div>
</div>
