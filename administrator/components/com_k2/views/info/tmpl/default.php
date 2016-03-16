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

<form action="index.php" method="post" name="adminForm">
	<div id="k2InfoPage">
		<ul class="k2ScrollSpyMenu">
			<li><a href="#k2About"><?php echo JText::_('K2_ABOUT'); ?><i class="right fa fa-angle-right"></i></a></li>
			<li><a href="#k2Credits"><?php echo JText::_('K2_CREDITS'); ?><i class="right fa fa-angle-right"></i></a></li>
			<li><a href="#k2SysInfo"><?php echo JText::_('K2_SYSTEM_INFORMATION'); ?><i class="right fa fa-angle-right"></i></a></li>
			<li><a href="#k2Permissions"><?php echo JText::_('K2_DIRECTORY_PERMISSIONS'); ?><i class="right fa fa-angle-right"></i></a></li>
			<li><a href="#k2Modules"><?php echo JText::_('K2_MODULES'); ?><i class="right fa fa-angle-right"></i></a></li>
			<li><a href="#k2Plugins"><?php echo JText::_('K2_PLUGINS'); ?><i class="right fa fa-angle-right"></i></a></li>
			<li><a href="#k2ThirdParty"><?php echo JText::_('K2_THIRDPARTY_PLUGIN_INFORMATION'); ?><i class="right fa fa-angle-right"></i></a></li>
		</ul>

		<div class="k2ScrollingContent xmlParamsFields">
			<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
				<tr>
					<td>
						<fieldset class="adminform" id="k2About">
							<legend><?php echo JText::_('K2_ABOUT'); ?></legend>
							<div class="k2TextBox"><?php echo JText::_('K2_ABOUT_TEXT'); ?></div>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td>
					  <fieldset class="adminform" id="k2Credits">
							<legend><?php echo JText::_('K2_CREDITS'); ?></legend>
							<table class="table stripped table-striped">
								<thead>
					        <tr>
					        	<th><?php echo JText::_('K2_PROVIDER'); ?></th>
					          <th><?php echo JText::_('K2_VERSION'); ?></th>
					          <th><?php echo JText::_('K2_TYPE'); ?></th>
					          <th><?php echo JText::_('K2_LICENSE'); ?></th>
					        </tr>
					      </thead>
					      <tfoot>
					        <tr>
					          <th colspan="4">&nbsp;</th>
					        </tr>
					      </tfoot>
							  <tbody>
							    <tr>
							      <td><a target="_blank" href="http://pear.php.net/package/Services_JSON/">Services_JSON</a></td>
							      <td>1.0.1</td>
							      <td><?php echo JText::_('K2_PHP_CLASS'); ?></td>
							      <td><?php echo JText::_('K2_BSD'); ?></td>
							    </tr>
							    <tr>
							      <td><a target="_blank" href="http://www.verot.net/php_class_upload.htm">class.upload.php</a></td>
							      <td>0.33dev</td>
							      <td><?php echo JText::_('K2_PHP_CLASS'); ?></td>
							      <td><?php echo JText::_('K2_GNUGPL'); ?></td>
							    </tr>
							    <tr>
							      <td><a target="_blank" href="http://jquery.com">jQuery</a></td>
							      <td>1.3.x - 1.12.x</td>
							      <td><?php echo JText::_('K2_JS_LIB'); ?></td>
							      <td><?php echo JText::_('K2_MIT'); ?></td>
							    </tr>
							    <tr>
							      <td><a target="_blank" href="http://jqueryui.com/">jQuery UI</a></td>
							      <td>1.8.24</td>
							      <td><?php echo JText::_('K2_JS_LIB'); ?></td>
							      <td><?php echo JText::_('K2_MIT'); ?></td>
							    </tr>
							    <tr>
							      <td><a target="_blank" href="http://elfinder.org/">elFinder</a></td>
							      <td>2.0 (rc1) [patched by JoomlaWorks]</td>
							      <td><?php echo JText::_('K2_INFO_FILE_MANAGER'); ?></td>
							      <td><?php echo JText::_('K2_BSD'); ?></td>
							    </tr>
							    <tr>
							      <td><a target="_blank" href="https://select2.github.io/">Select2</a></td>
							      <td></td>
							      <td><?php echo JText::_('K2_INFO_REPLACEMENT_FOR_SELECT_BOXES'); ?></td>
							      <td><?php echo JText::_('K2_MIT'); ?></td>
							    </tr>
							    <tr>
							      <td><a target="_blank" href="http://dimsemenov.com/plugins/magnific-popup/">Magnific Popup</a></td>
							      <td>1.1.0</td>
							      <td><?php echo JText::_('K2_INFO_RESPONSIVE_LIGHTBOX_DIALOG_SCRIPT'); ?></td>
							      <td><?php echo JText::_('K2_MIT'); ?></td>
							    </tr>
							    <tr>
							      <td><a target="_blank" href="http://nicedit.com/">NicEdit</a></td>
							      <td></td>
							      <td><?php echo JText::_('K2_INFO_WYSIWYG_EDITOR'); ?></td>
							      <td><?php echo JText::_('K2_MIT'); ?></td>
							    </tr>
							  </tbody>
							</table>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td>
					  <fieldset class="adminform" id="k2SysInfo">
					    <legend><?php echo JText::_('K2_SYSTEM_INFORMATION'); ?></legend>
					    <table class="k2InfoTable table stripped  table-striped">
					      <thead>
					        <tr>
					          <th><?php echo JText::_('K2_CHECK'); ?></th>
					          <th><?php echo JText::_('K2_RESULT'); ?></th>
					        </tr>
					      </thead>
					      <tfoot>
					        <tr>
					          <th colspan="2">&nbsp;</th>
					        </tr>
					      </tfoot>
					      <tbody>
					        <tr>
					          <td><strong><?php echo JText::_('K2_WEB_SERVER'); ?></strong></td>
					          <td><?php echo $this->server; ?></td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_PHP_VERSION'); ?></strong></td>
					          <td><?php echo $this->php_version; ?></td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_MYSQL_VERSION'); ?></strong></td>
					          <td><?php echo $this->db_version; ?></td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_GD_IMAGE_LIBRARY'); ?></strong></td>
					          <td><?php if ($this->gd_check) { $gdinfo=gd_info(); echo $gdinfo["GD Version"]; } else echo JText::_('K2_DISABLED'); ?></td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_MULTIBYTE_STRING_SUPPORT'); ?></strong></td>
					          <td><?php if ($this->mb_check) echo JText::_('K2_ENABLED'); else echo JText::_('K2_DISABLED'); ?></td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_UPLOAD_LIMIT'); ?></strong></td>
					          <td><?php echo ini_get('upload_max_filesize'); ?></td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_MEMORY_LIMIT'); ?></strong></td>
					          <td><?php echo ini_get('memory_limit'); ?></td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_OPEN_REMOTE_FILES_ALLOW_URL_FOPEN'); ?></strong></td>
					          <td><?php echo (ini_get('allow_url_fopen')) ? JText::_('K2_YES') : JText::_('K2_NO'); ?></td>
					        </tr>
					      </tbody>
					    </table>
					  </fieldset>
					</td>
				</tr>
				<tr>
					<td>
					  <fieldset class="adminform" id="k2Permissions">
					    <legend><?php echo JText::_('K2_DIRECTORY_PERMISSIONS'); ?></legend>
					    <table class="k2InfoTable table stripped table-striped">
					      <thead>
					        <tr>
					          <th><?php echo JText::_('K2_CHECK'); ?></th>
					          <th><?php echo JText::_('K2_RESULT'); ?></th>
					        </tr>
					      </thead>
					      <tfoot>
					        <tr>
					          <th colspan="2">&nbsp;</th>
					        </tr>
					      </tfoot>
					      <tbody>
					        <tr>
					          <td><strong>media/k2</strong></td>
					          <td>
								<?php if ($this->media_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>media/k2/attachments</strong></td>
					          <td>
								<?php if ($this->attachments_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>media/k2/categories</strong></td>
					          <td>
								<?php if ($this->categories_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>media/k2/galleries</strong></td>
					          <td>
								<?php if ($this->galleries_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>media/k2/items</strong></td>
					          <td>
								<?php if ($this->items_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>media/k2/users</strong></td>
					          <td>
								<?php if ($this->users_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>media/k2/videos</strong></td>
					          <td>
								<?php if ($this->videos_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>cache</strong></td>
					          <td>
								<?php if ($this->cache_folder_check): ?>
									<span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
								<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					      </tbody>
					    </table>
					  </fieldset>
					</td>
				</tr>
				<tr>
					<td>
					  <fieldset class="adminform" id="k2Modules">
					    <legend><?php echo JText::_('K2_MODULES'); ?></legend>
					    <table class="k2InfoTable table stripped table-striped">
					      <thead>
					        <tr>
					          <th><?php echo JText::_('K2_CHECK'); ?></th>
					          <th><?php echo JText::_('K2_RESULT'); ?></th>
					        </tr>
					      </thead>
					      <tfoot>
					        <tr>
					          <th colspan="2">&nbsp;</th>
					        </tr>
					      </tfoot>
					      <tbody>
					        <tr>
					          <td><strong>mod_k2_comments</strong></td>
					          <td>
					          	<?php if (is_null(JModuleHelper::getModule('mod_k2_comments'))): ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
								<?php else: ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
								<?php endif; ?>

					          </td>
					        </tr>
					        <tr>
					          <td><strong>mod_k2_content</strong></td>
					          <td>
					          	<?php if (is_null(JModuleHelper::getModule('mod_k2_content'))): ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
								<?php else: ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
								<?php endif; ?>
					          </td>
					        </tr>
					        <tr>
					          <td><strong>mod_k2_tools</strong></td>
					          <td>
					          	<?php if (is_null(JModuleHelper::getModule('mod_k2_tools'))): ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
								<?php else: ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
								<?php endif; ?>
					          	</td>
					        </tr>
					        <tr>
					          <td><strong>mod_k2_user</strong></td>
					          <td>
					          	<?php if (is_null(JModuleHelper::getModule('mod_k2_user'))): ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
								<?php else: ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
								<?php endif; ?>
					          	</td>
					        </tr>
					        <tr>
					          <td><strong>mod_k2_users</strong></td>
					          <td>
					          	<?php if (is_null(JModuleHelper::getModule('mod_k2_users'))): ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
								<?php else: ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
								<?php endif; ?>
					          	</td>
					        </tr>
					        <tr>
					          <td><strong>mod_k2_quickicons</strong> (administrator)</td>
					          <td>
					          	<?php if (is_null(JModuleHelper::getModule('mod_k2_quickicons'))): ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
								<?php else: ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
								<?php endif; ?>
					          	</td>
					        </tr>
					        <tr>
					          <td><strong>mod_k2_stats</strong> (administrator)</td>
					          <td>
					          	<?php if (is_null(JModuleHelper::getModule('mod_k2_stats'))): ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
								<?php else: ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
								<?php endif; ?>

					          	</td>
					        </tr>
					      </tbody>
					    </table>
					  </fieldset>
					</td>
				</tr>
				<tr>
					<td>
					  <fieldset class="adminform" id="k2Plugins">
					    <legend><?php echo JText::_('K2_PLUGINS'); ?></legend>
					    <table class="k2InfoTable table stripped table-striped">
					      <thead>
					        <tr>
					          <th><?php echo JText::_('K2_CHECK'); ?></th>
					          <th><?php echo JText::_('K2_RESULT'); ?></th>
					        </tr>
					      </thead>
					      <tfoot>
					        <tr>
					          <th colspan="2">&nbsp;</th>
					        </tr>
					      </tfoot>
					      <tbody>
					        <?php if(version_compare(JVERSION,'2.5.0','ge')): ?>
					        <tr>
					          <td><strong>Finder - K2</strong></td>
					          <td>
					          	<?php echo (JFile::exists(JPATH_PLUGINS.DS.'finder'.DS.'k2.php') || JFile::exists(JPATH_PLUGINS.DS.'finder'.DS.'k2'.DS.'k2.php'))?'<span class="green">'.JText::_('K2_INSTALLED'):'<span class="red">'.JText::_('K2_NOT_INSTALLED'); ?></span>
					          	- <?php echo (JPluginHelper::isEnabled('finder', 'k2'))? '<span class="green">'.JText::_('K2_ENABLED'): '<span class="red">'.JText::_('K2_DISABLED'); ?><span></td>
					        </tr>
					        <?php endif; ?>
					        <tr>
					          <td><strong>Search - K2</strong></td>
					          <td>
					          	<?php echo (JFile::exists(JPATH_PLUGINS.DS.'search'.DS.'k2.php') || JFile::exists(JPATH_PLUGINS.DS.'search'.DS.'k2'.DS.'k2.php'))?'<span class="green">'.JText::_('K2_INSTALLED'):'<span class="red">'.JText::_('K2_NOT_INSTALLED'); ?></span>
					          	- <?php echo (JPluginHelper::isEnabled('search', 'k2'))?'<span class="green">'.JText::_('K2_ENABLED'): '<span class="red">'.JText::_('K2_DISABLED'); ?><span></td>
					        </tr>
					        <tr>
					          <td><strong>System - K2</strong></td>
					          <td>
					          	<?php echo (JFile::exists(JPATH_PLUGINS.DS.'system'.DS.'k2.php') || JFile::exists(JPATH_PLUGINS.DS.'system'.DS.'k2'.DS.'k2.php'))?'<span class="green">'.JText::_('K2_INSTALLED'):'<span class="red">'.JText::_('K2_NOT_INSTALLED'); ?></span>
					          - <?php echo (JPluginHelper::isEnabled('system', 'k2'))?'<span class="green">'.JText::_('K2_ENABLED'): '<span class="red">'.JText::_('K2_DISABLED'); ?><span></td>
					        </tr>
					        <tr>
					          <td><strong>User - K2</strong></td>
					          <td>
					          	<?php echo (JFile::exists(JPATH_PLUGINS.DS.'user'.DS.'k2.php') || JFile::exists(JPATH_PLUGINS.DS.'user'.DS.'k2'.DS.'k2.php'))?'<span class="green">'.JText::_('K2_INSTALLED'):'<span class="red">'.JText::_('K2_NOT_INSTALLED'); ?></span>
					          	- <?php echo (JPluginHelper::isEnabled('user', 'k2'))?'<span class="green">'.JText::_('K2_ENABLED'): '<span class="red">'.JText::_('K2_DISABLED'); ?><span></td>
					        </tr>
					      </tbody>
					    </table>
					  </fieldset>
					</td>
				</tr>
				<tr>
					<td>
					  <fieldset class="adminform" id="k2ThirdParty">
					    <legend><?php echo JText::_('K2_THIRDPARTY_PLUGIN_INFORMATION'); ?></legend>
					    <table class="k2InfoTable table table-striped stripped ">
					      <thead>
					        <tr>
					          <th><?php echo JText::_('K2_CHECK'); ?></th>
					          <th><?php echo JText::_('K2_RESULT'); ?></th>
					        </tr>
					      </thead>
					      <tfoot>
					        <tr>
					          <th colspan="2">&nbsp;</th>
					        </tr>
					      </tfoot>
					      <tbody>
					        <tr>
					          <td><strong><?php echo JText::_('K2_ALLVIDEOS_PLUGIN'); ?></strong></td>
					          <td><?php
									if (JFile::exists(JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos.php') || JFile::exists(JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos'.DS.'jw_allvideos.php')) :?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
									<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
									<?php endif; ?>
								</td>
					        </tr>
					        <tr>
					          <td><strong><?php echo JText::_('K2_SIMPLE_IMAGE_GALLERY_PRO_PLUGIN'); ?></strong></td>
					          <td><?php
									if (JFile::exists(JPATH_PLUGINS.DS.'content'.DS.'jw_sigpro.php') || JFile::exists(JPATH_PLUGINS.DS.'content'.DS.'jw_sigpro'.DS.'jw_sigpro.php')): ?>
									<span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
									<?php else: ?>
									<span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
									<?php endif; ?>
								</td>
					        </tr>
					      </tbody>
					    </table>
					  </fieldset>
					</td>
				</tr>
			</table>
		</div>
	</div>
</form>
