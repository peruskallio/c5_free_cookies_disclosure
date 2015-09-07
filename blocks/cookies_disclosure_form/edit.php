<?php  defined('C5_EXECUTE') or die(_("Access Denied."));
$form = Loader::helper('form');
?>
<div class="ccm-ui">
	<fieldset>
		<h3><?php  echo t('Options') ?></h3>
		<div class="ccm-block-field-group">
			<div class="input">
				<ul class="inputs-list">
					<li><label><?php  echo $form->checkbox('showCheckbox', 1, $showCheckbox == 1) ?> <?php  echo t('Show Checkbox') ?></label></li>
					<li><label><?php  echo $form->checkbox('ajaxSubmit', 1, $ajaxSubmit == 1) ?> <?php  echo t('AJAX Submit') ?></label></li>
				</ul>
			</div>
		</div>
		<div class="ccm-block-field-group">
			<div class="clearfix">
				<label><?php  echo t("Accept Text") ?></label>
				<div class="input">
					<?php  echo $form->text('acceptText', $acceptText) ?>
					<span class="help-inline"><?php  echo t("Leave empty if you don't want to show this") ?></span>
				</div>
			</div>
		</div>
		<div class="ccm-block-field-group">
			<div class="clearfix">
				<label><?php  echo t("Button Text") ?></label>
				<div class="input">
					<?php  echo $form->text('submitText', $submitText) ?>
				</div>
			</div>
		</div>
	</fieldset>
</div>
