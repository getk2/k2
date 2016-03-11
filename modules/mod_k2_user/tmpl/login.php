<?php
/**
 * @version    2.7.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2LoginBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
	<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login">
		<?php if($params->get('pretext')): ?>
		<p class="preText"><?php echo $params->get('pretext'); ?></p>
	  <?php endif; ?>

	  <fieldset class="input">
	    <p id="form-login-username">
	      <label for="modlgn_username"><?php echo JText::_('K2_USERNAME') ?></label>
	      <input id="modlgn_username" type="text" name="username" class="inputbox" size="18" />
	    </p>
	    <p id="form-login-password">
	      <label for="modlgn_passwd"><?php echo JText::_('K2_PASSWORD') ?></label>
	      <input id="modlgn_passwd" type="password" name="<?php echo $passwordFieldName; ?>" class="inputbox" size="18" />
	    </p>
	    <?php if(JPluginHelper::isEnabled('system', 'remember')): ?>
	    <p id="form-login-remember">
	      <label for="modlgn_remember"><?php echo JText::_('K2_REMEMBER_ME') ?></label>
	      <input id="modlgn_remember" type="checkbox" name="remember" class="inputbox" value="yes" />
	    </p>
	    <?php endif; ?>

	    <input type="submit" name="Submit" class="button" value="<?php echo JText::_('K2_LOGIN') ?>" />
	  </fieldset>

	  <ul>
	    <li><a href="<?php echo $resetLink; ?>"><?php echo JText::_('K2_FORGOT_YOUR_PASSWORD'); ?></a></li>
	    <li><a href="<?php echo $remindLink ?>"><?php echo JText::_('K2_FORGOT_YOUR_USERNAME'); ?></a></li>
	    <?php if ($usersConfig->get('allowUserRegistration')): ?>
	    <li><a href="<?php echo $registrationLink; ?>"><?php echo JText::_('K2_CREATE_AN_ACCOUNT'); ?></a></li>
	    <?php endif; ?>
	  </ul>

	  <?php if($params->get('posttext')): ?>
	  <p class="postText"><?php echo $params->get('posttext'); ?></p>
	  <?php endif; ?>

	  <input type="hidden" name="option" value="<?php echo $option; ?>" />
	  <input type="hidden" name="task" value="<?php echo $task; ?>" />
	  <input type="hidden" name="return" value="<?php echo $return; ?>" />
	  <?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
