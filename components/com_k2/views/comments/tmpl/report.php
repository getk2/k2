<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<div class="k2ReportCommentFormContainer">
	<h2 class="componentheading">
		<?php echo JText::_('K2_REPORT_COMMENT'); ?>
	</h2>
	<blockquote class="commentPreview">
		<span class="quoteIconLeft">&ldquo;</span>
		<span class="theComment"><?php echo nl2br($this->row->commentText); ?></span>
		<span class="quoteIconRight">&rdquo;</span>
	</blockquote>
	<form action="<?php echo JURI::root(true); ?>/index.php" name="k2ReportCommentForm" id="k2ReportCommentForm" method="post">
		<label for="name"><?php echo JText::_('K2_YOUR_NAME'); ?></label>
		<input type="text" id="name" name="name" value="" />

		<label for="reportReason"><?php echo JText::_('K2_REPORT_REASON'); ?></label>
		<textarea name="reportReason" id="reportReason" cols="60" rows="10"></textarea>

		<?php if($this->params->get('recaptcha') && $this->user->guest): ?>
		<?php if(!$this->params->get('recaptchaV2')): ?>
		<label class="formRecaptcha"><?php echo JText::_('K2_ENTER_THE_TWO_WORDS_YOU_SEE_BELOW'); ?></label>
		<?php endif; ?>
		<div id="recaptcha" class="<?php echo $this->recaptchaClass; ?>"></div>
		<?php endif; ?>

		<button class="button"><?php echo JText::_('K2_SEND_REPORT'); ?></button>

		<span id="formLog"></span>

		<input type="hidden" name="option" value="com_k2" />
		<input type="hidden" name="view" value="comments" />
		<input type="hidden" name="task" value="sendReport" />
		<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
		<input type="hidden" name="format" value="raw" />
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
