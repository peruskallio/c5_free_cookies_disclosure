<?php 
defined('C5_EXECUTE') or die("Access Denied.");
$ih = Loader::helper('concrete/interface');
?>
<?php  echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Cookies Disclosure Settings'), false, false, false)?>
<form method="POST" action="<?php  echo $this->action("save_settings"); ?>">
<div class="ccm-pane-body">
	<fieldset>
		<legend><?php  echo t('Display Settings') ?></legend>
		<div class="clearfix">
			<?php  echo $form->label('alignment', t('Alignment')) ?>
			<div class="input">
				<?php  echo $form->select('alignment', array('top' => t('Top of the Page'), 'bottom' => t('Bottom of the Page')), $alignment); ?>
			</div>
		</div>
		<div class="clearfix">
			<?php  echo $form->label('color_profile', t('Color Profile')) ?>
			<div class="input">
				<?php  echo $form->select('color_profile', $colorProfiles, $colorProfile); ?>
			</div>
		</div>
		<div id="color_profile_settings">
			<div class="options options-custom">
				<div class="clearfix">
					<?php  echo $form->label('color_profile_custom', t('CSS Suffix for Color Profile')) ?>
					<div class="input">
						<?php  echo $form->text('color_profile_custom', $colorProfileCustom); ?>
						<span class="help-inline"><?php  echo t("CSS file named cookies_disclosure_SUFFIX.css will be loaded from your {root}/css directory.") ?></span>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix">
			<?php  echo $form->label('hide_interval', t('Hide Interval')) ?>
			<div class="input">
				<?php  echo $form->text('hide_interval', $hideInterval, array('style' => 'width:80px;')); ?>
				<span class="help-inline"><?php  echo t("In seconds (leave empty if you don't want to hide the notification)") ?></span>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend><?php  echo t('Debug') ?></legend>
		<div class="clearfix">
			<div class="input">
				<ul class="inputs-list">
					<li><label><?php  echo $form->checkbox('debug', 1, $debug == 1) ?> <span><?php  echo t('Enable Debug Mode') ?></span></label></li>
				</ul>
				<div class="help-block">
					<?php  echo t("When debug mode is enabled, the notification is visible although user would've already allowed cookies.") ?><br/>
					<?php  echo t("This is handy when styling the debug notification box for your theme.") ?>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="clearfix">
		<h3><?php  echo t('Customize Notification Content') ?></h3>
		<?php  if ($hasMultilingual) : ?>
			<p><?php  echo t('If you want to customize the notification message, just create a stack for each language named: "%s" and add your content there.', t('Cookies Disclosure - LANG')) ?></p>
			<p><?php  echo t('Replace the "LANG" with the 2-letter language code in uppercase you want to show the notification to.') ?></p>
			<p><?php  echo t("For example, if you're running your site in English and Finnish, create the following stacks:") ?></p>
			<ul>
				<li><?php  echo t('Cookies Disclosure - EN') ?></li>
				<li><?php  echo t('Cookies Disclosure - FI') ?></li>
			</ul>
		<?php  else : ?>
			<p><?php  echo t('If you want to customize the notification message, just create a stack named "%s" and add your content there.', t('Cookies Disclosure')) ?></p>
		<?php  endif; ?>
		<?php  echo $ih->button(t('Go to Stacks &raquo;'), $this->url('/dashboard/blocks/stacks/'), 'left') ?>
	</div>
</div>
<div class="ccm-pane-footer">
	<?php  echo $ih->submit(t('Save'), 'save', 'right', 'submit-button btn primary') ?>
</div>
</form>
<?php  echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('#color_profile_settings .options').css('display', 'none');
	var inp = $('select[name="color_profile"]');
	function _change() {
		$('#color_profile_settings .options').css('display', 'none');
		$('#color_profile_settings .options-'+inp.val()).css('display', 'block');
	}
	inp.change(function() {
		_change();
	});
	_change();
});
</script>
