<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$acceptText = trim($acceptText);
?>
<div class="disclosure-form">
    <form action="<?php echo View::url('/ccm/free_cookies_disclosure/set_cookie') ?>" method="POST">
        <?php if ($showCheckbox) : ?>
            <div class="input-checkbox">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="allowCookies" value="1">
                        <?php echo $acceptText ?>
                    </label>
                </div>
            </div>
        <?php else : ?>
            <input type="hidden" name="allowCookies" value="1">
            <?php if (strlen($acceptText) > 0) : ?>
                <div class="accept-text">
                    <p><?php echo $acceptText ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="button">
            <button type="submit" class="btn btn-default"><?php echo $submitText ?></button>
        </div>
    </form>
</div>