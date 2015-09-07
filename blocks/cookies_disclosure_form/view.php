<?php  defined('C5_EXECUTE') or die(_("Access Denied."));
$acceptText = trim($acceptText);
?>
<div class="disclosure-form">
	<form action="<?php  echo View::url('/cookies_disclosure') ?>" method="POST">
		<?php  if ($showCheckbox) : ?>
			<div class="input-checkbox">
				<p>
					<label>
						<?php  if (strlen($acceptText) > 0) : ?>
							<span class="text"><?php  echo $acceptText ?></span>
						<?php  endif; ?>
						<span class="input"><input type="checkbox" name="allowCookies" value="1" /></span>
					</label>
				</p>
			</div>
		<?php  else : ?>
			<input type="hidden" name="allowCookies" value="1" />
			<?php  if (strlen($acceptText) > 0) :?>
				<div class="accept-text">
					<p><?php  echo $acceptText ?></p>
				</div>
			<?php  endif; ?>
		<?php  endif; ?>
		<div class="button">
			<input type="submit" name="submit" value="<?php  echo $submitText ?>" />
		</div>
	</form>
</div>