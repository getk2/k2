<?php
/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
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

        <div class="k2ScrollingContent xmlParamsFields k2GenericForm">
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
                <tr>
                    <td>
                        <div class="adminform" id="k2About">
                            <h3><?php echo JText::_('K2_ABOUT'); ?></h3>
                            <div class="k2TextBox"><?php echo JText::_('K2_ABOUT_TEXT'); ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="adminform" id="k2Credits">
                            <h3><?php echo JText::_('K2_CREDITS'); ?></h3>
                            <table class="k2InfoTable table stripped table-striped">
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
                                        <td><a target="_blank" href="https://github.com/verot/class.upload.php">class.upload.php</a></td>
                                        <td>0.34dev (modified by JoomlaWorks)</td>
                                        <td><?php echo JText::_('K2_PHP_LIBRARY'); ?></td>
                                        <td>GNU/GPL v2</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/achingbrain/php5-akismet">PHP5 Akismet</a></td>
                                        <td>0.5</td>
                                        <td><?php echo JText::_('K2_PHP_LIBRARY'); ?></td>
                                        <td>BSD</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://jquery.com/">jQuery</a></td>
                                        <td>1.7.2 - 1.12.4</td>
                                        <td><?php echo JText::_('K2_JS_LIB'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://jqueryui.com/">jQuery UI</a></td>
                                        <td>1.8.24 &amp; 1.12.1</td>
                                        <td><?php echo JText::_('K2_JS_LIB'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/Studio-42/elFinder">elFinder</a></td>
                                        <td>2.1.61</td>
                                        <td><?php echo JText::_('K2_INFO_FILE_MANAGER'); ?></td>
                                        <td>BSD</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/select2/select2">Select2</a></td>
                                        <td>4.0.12</td>
                                        <td><?php echo JText::_('K2_INFO_REPLACEMENT_FOR_SELECT_BOXES'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/dimsemenov/Magnific-Popup">Magnific Popup</a></td>
                                        <td>1.1.0</td>
                                        <td><?php echo JText::_('K2_INFO_RESPONSIVE_LIGHTBOX_DIALOG_SCRIPT'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/fancyapps/fancybox">fancyBox</a></td>
                                        <td>3.5.7</td>
                                        <td><?php echo JText::_('K2_INFO_RESPONSIVE_LIGHTBOX_DIALOG_SCRIPT'); ?></td>
                                        <td>GNU/GPL v3</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/flatpickr/flatpickr">flatpickr</a></td>
                                        <td>4.5.7</td>
                                        <td><?php echo JText::_('K2_INFO_DATETIME_PICKER'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="http://www.nicedit.com/">NicEdit</a></td>
                                        <td>0.9 r25</td>
                                        <td><?php echo JText::_('K2_INFO_WYSIWYG_EDITOR'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/FortAwesome/Font-Awesome">Font Awesome</a></td>
                                        <td>4.7.0</td>
                                        <td><?php echo JText::_('K2_INFO_ICONS'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/thesabbir/simple-line-icons">Simple Line Icons</a></td>
                                        <td>2.4.1</td>
                                        <td><?php echo JText::_('K2_INFO_ICONS'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                    <tr>
                                        <td><a target="_blank" href="https://github.com/ionic-team/ionicons">Ionicons</a></td>
                                        <td>2.0.1</td>
                                        <td><?php echo JText::_('K2_INFO_ICONS'); ?></td>
                                        <td>MIT</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="adminform" id="k2SysInfo">
                            <h3><?php echo JText::_('K2_SYSTEM_INFORMATION'); ?></h3>
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
                                        <td><strong><?php echo JText::_('K2_WEB_SERVER'); ?></strong></td>
                                        <td><?php echo $this->server; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo JText::_('K2_PHP_VERSION'); ?></strong></td>
                                        <td><?php echo $this->php_version; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo JText::_('K2_DATABASE_VERSION'); ?></strong></td>
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
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="adminform" id="k2Permissions">
                            <h3><?php echo JText::_('K2_DIRECTORY_PERMISSIONS'); ?></h3>
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
                                        <td><strong>cache</strong></td>
                                        <td>
                                            <?php if ($this->cache_folder_check): ?>
                                            <span class="green"><?php echo JText::_('K2_WRITABLE'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_NOT_WRITABLE'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
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
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="adminform" id="k2Modules">
                            <h3><?php echo JText::_('K2_MODULES'); ?></h3>
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
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="adminform" id="k2Plugins">
                            <h3><?php echo JText::_('K2_PLUGINS'); ?></h3>
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
                                            <?php if (JFile::exists(JPATH_PLUGINS.'/finder/k2.php') || JFile::exists(JPATH_PLUGINS.'/finder/k2/k2.php')): ?>
                                            <span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
                                            <?php endif; ?>
                                            &nbsp;-&nbsp;
                                            <?php if (JPluginHelper::isEnabled('finder', 'k2')): ?>
                                            <span class="green"><?php echo JText::_('K2_ENABLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_DISABLED'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Search - K2</strong></td>
                                        <td>
                                            <?php if (JFile::exists(JPATH_PLUGINS.'/search/k2.php') || JFile::exists(JPATH_PLUGINS.'/search/k2/k2.php')): ?>
                                            <span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
                                            <?php endif; ?>
                                            &nbsp;-&nbsp;
                                            <?php if (JPluginHelper::isEnabled('search', 'k2')): ?>
                                            <span class="green"><?php echo JText::_('K2_ENABLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_DISABLED'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>System - K2</strong></td>
                                        <td>
                                            <?php if (JFile::exists(JPATH_PLUGINS.'/system/k2.php') || JFile::exists(JPATH_PLUGINS.'/system/k2/k2.php')): ?>
                                            <span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
                                            <?php endif; ?>
                                            &nbsp;-&nbsp;
                                            <?php if (JPluginHelper::isEnabled('system', 'k2')): ?>
                                            <span class="green"><?php echo JText::_('K2_ENABLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_DISABLED'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>User - K2</strong></td>
                                        <td>
                                            <?php if (JFile::exists(JPATH_PLUGINS.'/user/k2.php') || JFile::exists(JPATH_PLUGINS.'/user/k2/k2.php')): ?>
                                            <span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
                                            <?php endif; ?>
                                            &nbsp;-&nbsp;
                                            <?php if (JPluginHelper::isEnabled('user', 'k2')): ?>
                                            <span class="green"><?php echo JText::_('K2_ENABLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_DISABLED'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="adminform" id="k2ThirdParty">
                            <h3><?php echo JText::_('K2_THIRDPARTY_PLUGIN_INFORMATION'); ?></h3>
                            <table class="k2InfoTable table table-striped stripped">
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
                                        <td>
                                            <strong><?php echo JText::_('K2_ALLVIDEOS_PLUGIN'); ?></strong>
                                        </td>
                                        <td>
                                            <?php if (JFile::exists(JPATH_PLUGINS.'/content/jw_allvideos.php') || JFile::exists(JPATH_PLUGINS.'/content/jw_allvideos/jw_allvideos.php')) :?>
                                            <span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong><?php echo JText::_('K2_SIMPLE_IMAGE_GALLERY_PRO_PLUGIN'); ?></strong>
                                        </td>
                                        <td>
                                            <?php if (JFile::exists(JPATH_PLUGINS.'/content/jw_sigpro.php') || JFile::exists(JPATH_PLUGINS.'/content/jw_sigpro/jw_sigpro.php')): ?>
                                            <span class="green"><?php echo JText::_('K2_INSTALLED'); ?></span>
                                            <?php else: ?>
                                            <span class="red"><?php echo JText::_('K2_NOT_INSTALLED'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>
