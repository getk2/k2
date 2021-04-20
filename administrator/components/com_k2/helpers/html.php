<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2021 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

class K2HelperHTML
{
    public static function activeMenu($current)
    {
        $view = JRequest::getCmd('view', 'items');
        if ($current === $view) {
            return ' class="active"';
        }
    }

    public static function sidebarMenu()
    {
        $params = JComponentHelper::getParams('com_k2');
        $user = JFactory::getUser();
        $view = JRequest::getCmd('view');

        $editForms = array('item', 'category', 'tag', 'user', 'usergroup', 'extrafield', 'extrafieldsgroup');

        $sidebarMenu = '';

        if (in_array($view, $editForms)) {
            $sidebarMenu = '
            <ul class="k2-disabled">
                <li>
                    <span>'.JText::_('K2_ITEMS').'</span>
                </li>
                <li>
                    <span>'.JText::_('K2_CATEGORIES').'</span>
                </li>
            ';
            if (!$params->get('lockTags') || $user->gid > 23) {
                $sidebarMenu .= '
                <li>
                    <span>'.JText::_('K2_TAGS').'</span>
                </li>
                ';
            }
            $sidebarMenu .= '
                <li>
                    <span>'.JText::_('K2_COMMENTS').'</span>
                </li>
            ';
            if ($user->gid > 23) {
                $sidebarMenu .= '
                <li>
                    <span>'.JText::_('K2_USERS').'</span>
                </li>
                <li>
                    <span>'.JText::_('K2_USER_GROUPS').'</span>
                </li>
                <li>
                    <span>'.JText::_('K2_EXTRA_FIELDS').'</span>
                </li>
                <li>
                    <span>'.JText::_('K2_EXTRA_FIELD_GROUPS').'</span>
                </li>
                ';
            }
            $sidebarMenu .= '
                <li>
                    <span>'.JText::_('K2_MEDIA_MANAGER').'</span>
                </li>
                <li>
                    <span>'.JText::_('K2_INFORMATION').'</span>
                </li>
            ';
            if ($user->gid > 23) {
                $sidebarMenu .= '
                <li>
                    <span>'.JText::_('K2_SETTINGS').'</span>
                </li>
                ';
            }
            $sidebarMenu .= '
            </ul>
            ';
        } else {
            $sidebarMenu = '
            <ul>
                <li'.self::activeMenu('items').'>
                    <a href="index.php?option=com_k2&amp;view=items">'.JText::_('K2_ITEMS').'</a>
                </li>
                <li'.self::activeMenu('categories').'>
                    <a href="index.php?option=com_k2&amp;view=categories">'.JText::_('K2_CATEGORIES').'</a>
                </li>
            ';
            if (!$params->get('lockTags') || $user->gid > 23) {
                $sidebarMenu .= '
                <li'.self::activeMenu('tags').'>
                    <a href="index.php?option=com_k2&amp;view=tags">'.JText::_('K2_TAGS').'</a>
                </li>
                ';
            }
            $sidebarMenu .= '
                <li'.self::activeMenu('comments').'>
                    <a href="index.php?option=com_k2&amp;view=comments">'.JText::_('K2_COMMENTS').'</a>
                </li>
            ';
            if ($user->gid > 23) {
                $sidebarMenu .= '
                <li'.self::activeMenu('users').'>
                    <a href="index.php?option=com_k2&amp;view=users">'.JText::_('K2_USERS').'</a>
                </li>
                <li'.self::activeMenu('usergroups').'>
                    <a href="index.php?option=com_k2&amp;view=usergroups">'.JText::_('K2_USER_GROUPS').'</a>
                </li>
                <li'.self::activeMenu('extrafields').'>
                    <a href="index.php?option=com_k2&amp;view=extrafields">'.JText::_('K2_EXTRA_FIELDS').'</a>
                </li>
                <li'.self::activeMenu('extrafieldsgroups').'>
                    <a href="index.php?option=com_k2&amp;view=extrafieldsgroups">'.JText::_('K2_EXTRA_FIELD_GROUPS').'</a>
                </li>
                ';
            }
            $sidebarMenu .= '
                <li'.self::activeMenu('media').'>
                    <a href="index.php?option=com_k2&amp;view=media">'.JText::_('K2_MEDIA_MANAGER').'</a>
                </li>
                <li'.self::activeMenu('info').'>
                    <a href="index.php?option=com_k2&amp;view=info">'.JText::_('K2_INFORMATION').'</a>
                </li>
            ';
            if ($user->gid > 23) {
                if (K2_JVERSION == '15') {
                    $settingsURL = 'index.php?option=com_k2&view=settings';
                    $settingsURLAttributes = ' class="modal" rel="{handler: \'iframe\', size: {x: (window.innerWidth) * 0.7, y: (window.innerHeight) * 0.9}}"';
                } elseif (K2_JVERSION == '25') {
                    $settingsURL = 'index.php?option=com_config&view=component&component=com_k2&path=&tmpl=component';
                    $settingsURLAttributes = ' class="modal" rel="{handler: \'iframe\', size: {x: (window.innerWidth) * 0.7, y: (window.innerHeight) * 0.9}}"';
                } else {
                    $settingsURL = 'index.php?option=com_config&view=component&component=com_k2&path=&return='.urlencode(base64_encode(JFactory::getURI()->toString()));
                    $settingsURLAttributes = '';
                }
                $sidebarMenu .= '
                <li>
                    <a href="'.$settingsURL.'"'.$settingsURLAttributes.'>'.JText::_('K2_SETTINGS').'</a>
                </li>
                ';
            }
            $sidebarMenu .= '
            </ul>
            ';
        }

        return $sidebarMenu;
    }

    public static function mobileMenu()
    {
        $params = JComponentHelper::getParams('com_k2');
        $user = JFactory::getUser();
        $view = JRequest::getCmd('view');
        $context = JRequest::getCmd('context');

        $editForms = array('item', 'category', 'tag', 'user', 'usergroup', 'extrafield', 'extrafieldsgroup', 'media');

        $mobileMenu = '';

        if (!in_array($view, $editForms) && $context != 'modalselector' && $view != 'settings') {
            $mobileMenu = '
            <div id="k2AdminMobileMenu">
                <ul>
                    <li'.self::activeMenu('items').'>
                        <a href="index.php?option=com_k2&amp;view=items"><i class="fa fa-list-alt" aria-hidden="true"></i><span>'.JText::_('K2_ITEMS').'</span></a>
                    </li>
                    <li'.self::activeMenu('categories').'>
                        <a href="index.php?option=com_k2&amp;view=categories"><i class="fa fa-folder-open-o" aria-hidden="true"></i><span>'.JText::_('K2_CATEGORIES').'</span></a>
                    </li>
                    <li class="k2ui-add">
                        <a href="index.php?option=com_k2&amp;view=item"><i class="fa fa-plus-square-o" aria-hidden="true"></i><span>'.JText::_('K2_ADD_ITEM').'</span></a>
                    </li>
            ';
            if (!$params->get('lockTags') || $user->gid > 23) {
                $mobileMenu .= '
                    <li'.self::activeMenu('tags').'>
                        <a href="index.php?option=com_k2&amp;view=tags"><i class="fa fa-tags" aria-hidden="true"></i><span>'.JText::_('K2_TAGS').'</span></a>
                    </li>
                ';
            }
            $mobileMenu .= '
                    <li'.self::activeMenu('comments').'>
                        <a href="index.php?option=com_k2&amp;view=comments"><i class="fa fa-comments-o" aria-hidden="true"></i><span>'.JText::_('K2_COMMENTS').'</span></a>
                    </li>
                </ul>
            </div>
            ';
        }

        return $mobileMenu;
    }

    public static function subMenu()
    {
        return; /* Disable the old sidebar menu */

        $params = JComponentHelper::getParams('com_k2');
        $user = JFactory::getUser();
        $view = JRequest::getCmd('view');

        JSubMenuHelper::addEntry(JText::_('K2_ITEMS'), 'index.php?option=com_k2&view=items', $view == 'items');
        JSubMenuHelper::addEntry(JText::_('K2_CATEGORIES'), 'index.php?option=com_k2&view=categories', $view == 'categories');
        if (!$params->get('lockTags') || $user->gid > 23) {
            JSubMenuHelper::addEntry(JText::_('K2_TAGS'), 'index.php?option=com_k2&view=tags', $view == 'tags');
        }
        JSubMenuHelper::addEntry(JText::_('K2_COMMENTS'), 'index.php?option=com_k2&view=comments', $view == 'comments');
        if ($user->gid > 23) {
            JSubMenuHelper::addEntry(JText::_('K2_USERS'), 'index.php?option=com_k2&view=users', $view == 'users');
            JSubMenuHelper::addEntry(JText::_('K2_USER_GROUPS'), 'index.php?option=com_k2&view=usergroups', $view == 'usergroups');
            JSubMenuHelper::addEntry(JText::_('K2_EXTRA_FIELDS'), 'index.php?option=com_k2&view=extrafields', $view == 'extrafields');
            JSubMenuHelper::addEntry(JText::_('K2_EXTRA_FIELD_GROUPS'), 'index.php?option=com_k2&view=extrafieldsgroups', $view == 'extrafieldsgroups');
        }
        JSubMenuHelper::addEntry(JText::_('K2_MEDIA_MANAGER'), 'index.php?option=com_k2&view=media', $view == 'media');
        JSubMenuHelper::addEntry(JText::_('K2_INFORMATION'), 'index.php?option=com_k2&view=info', $view == 'info');
    }

    public static function stateToggler(&$row, $key, $property = 'published', $tasks = array('publish', 'unpublish'), $labels = array('K2_PUBLISH', 'K2_UNPUBLISH'))
    {
        $task = $row->$property ? $tasks[1] : $tasks[0];
        $action = $row->$property ? JText::_($labels[1]) : JText::_($labels[0]);
        $class = 'k2Toggler';
        $status = $row->$property ? 'k2Active' : 'k2Inactive';
        $href = '<a class="'.$class.' '.$status.'" href="javascript:void(0);" onclick="return listItemTask(\'cb'.$key.'\',\''.$task.'\')" title="'.$action.'">'.$action.'</a>';
        return $href;
    }

    public static function loadHeadIncludes($loadFramework = false, $jQueryUI = false, $adminHeadIncludes = false, $adminModuleIncludes = false)
    {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        $user = JFactory::getUser();

        $params = K2HelperUtilities::getParams('com_k2');

        $option = JRequest::getCmd('option');
        $view = strtolower(JRequest::getWord('view', 'items'));
        $task = JRequest::getCmd('task');

        $getSiteLanguage = JFactory::getLanguage();
        $languageTag = substr($getSiteLanguage->getTag(), 0, 2);

        $jQueryHandling = $params->get('jQueryHandling', '1.9.1');

        if ($document->getType() == 'html') {
            // JS framework loading
            if (version_compare(JVERSION, '1.6.0', 'lt')) {
                JHTML::_('behavior.mootools');
            }

            if ($loadFramework && $view != 'media') {
                if (version_compare(JVERSION, '1.6.0', 'ge')) {
                    JHtml::_('behavior.framework');
                }
            }

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                JHtml::_('jquery.framework');
            }

            // jQuery
            if (version_compare(JVERSION, '3.0.0', 'lt')) {
                // Frontend
                if ($app->isSite()) {
                    // B/C for saved old options
                    if ($option == 'com_k2' && $view == 'item' && $task == 'edit') {
                        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js');
                    } else {
                        if ($jQueryHandling) {
                            if ($jQueryHandling == '1.7remote') {
                                $jQueryHandling = '1.7.2';
                            }
                            if ($jQueryHandling == '1.8remote') {
                                $jQueryHandling = '1.8.3';
                            }
                            if ($jQueryHandling == '1.9remote') {
                                $jQueryHandling = '1.9.1';
                            }
                            if ($jQueryHandling == '1.10remote') {
                                $jQueryHandling = '1.10.2';
                            }
                            if ($jQueryHandling == '1.11remote') {
                                $jQueryHandling = '1.11.3';
                            }
                            if ($jQueryHandling == '1.12remote') {
                                $jQueryHandling = '1.12.4';
                            }
                            $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/'.$jQueryHandling.'/jquery.min.js');
                        }
                    }
                }

                // Backend
                if ($app->isAdmin()) {
                    if (($option == 'com_k2' && ($view == 'item' || $view == 'category')) || $option == 'com_menus') {
                        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js');
                    } else {
                        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js');
                    }
                }
            }

            // jQueryUI
            if ($jQueryUI) {
                // Load version 1.8.24 for tabs & sortables (called the "old" way)...
                if (($option == 'com_k2' && ($view == 'item' || $view == 'category')) || $option == 'com_menus') {
                    $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js');
                }

                // Load latest version for the "media" view & modules only
                if (($option == 'com_k2' && $view == 'media') || $option == 'com_modules' || $option == 'com_advancedmodules') {
                    $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css');
                    $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
                }
            }

            // Everything else...
            if ($app->isAdmin() || $adminHeadIncludes) {
                // JS
                $isBackend = ($app->isAdmin()) ? ' k2IsBackend' : '';
                $isTask = ($task) ? ' k2TaskIs'.ucfirst($task) : '';
                $cssClass = 'isJ'.K2_JVERSION.' k2ViewIs'.ucfirst($view).''.$isTask.''.$isBackend;
                $document->addScriptDeclaration("

                    // Set K2 version as global JS variable
                    K2JVersion = '".K2_JVERSION."';

                    // Set Joomla version as class in the 'html' tag
                    (function(){
                        var addedClass = '".$cssClass."';
                        if (document.getElementsByTagName('html')[0].className !== '') {
                            document.getElementsByTagName('html')[0].className += ' '+addedClass;
                        } else {
                            document.getElementsByTagName('html')[0].className = addedClass;
                        }
                    })();

                    // K2 Language Strings
                    var K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST = '".JText::_('K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST', true)."';
                    var K2_REMOVE_THIS_ENTRY = '".JText::_('K2_REMOVE_THIS_ENTRY', true)."';
                    var K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST = '".JText::_('K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST', true)."';

                ");
                $document->addScript(JURI::root(true).'/media/k2/assets/js/k2.backend.js?v='.K2_CURRENT_VERSION.'&b='.K2_BUILD_ID.'&sitepath='.JURI::root(true).'/');

                // NicEdit
                if ($option == 'com_k2' && $view == 'item') {
                    $document->addScript(JURI::root(true).'/media/k2/assets/vendors/bkirchoff/nicedit/nicEdit.js?v='.K2_CURRENT_VERSION);
                }

                // Media (elFinder)
                if ($view == 'media') {
                    $document->addStyleSheet(JURI::root(true).'/media/k2/assets/vendors/studio-42/elfinder/css/elfinder.min.css?v='.K2_CURRENT_VERSION);
                    $document->addStyleSheet(JURI::root(true).'/media/k2/assets/vendors/studio-42/elfinder/css/theme.css?v='.K2_CURRENT_VERSION);
                    $document->addScript(JURI::root(true).'/media/k2/assets/vendors/studio-42/elfinder/js/elfinder.min.js?v='.K2_CURRENT_VERSION);
                } else {
                    JHTML::_('behavior.tooltip');
                    if (version_compare(JVERSION, '3.0.0', 'ge')) {
                        if ($params->get('taggingSystem') === '0' || $params->get('taggingSystem') === '1') {
                            // B/C - Convert old options
                            $whichTaggingSystem = ($params->get('taggingSystem')) ? 'free' : 'selection';
                            $params->set('taggingSystem', $whichTaggingSystem);
                        }
                        if ($view == 'item' && $params->get('taggingSystem') == 'selection') {
                            JHtml::_('formbehavior.chosen', 'select:not(#selectedTags, #tags)');
                        } else {
                            JHtml::_('formbehavior.chosen', 'select');
                        }
                    }
                }

                // Flatpickr
                if ($view == 'item' || $view == 'extrafield') {
                    $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.5.7/flatpickr.min.css');
                    $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.5.7/flatpickr.min.js');
                    if ($languageTag != 'en') {
                        if ($languageTag == 'el') {
                            $languageTag = 'gr';
                        }
                        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.5.7/l10n/'.$languageTag.'.js');
                        $document->addScriptDeclaration('
                            /* K2 - Flatpickr Localization */
                            flatpickr.localize(flatpickr.l10ns.'.$languageTag.');
                        ');
                    }
                    $document->addCustomTag('<!--[if IE 9]><link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.5.7/ie.css" /><![endif]-->');
                }

                // Magnific Popup
                $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css');
                $document->addStyleDeclaration('
                    /* K2 - Magnific Popup Overrides */
                    .mfp-iframe-holder {padding:10px;}
                    .mfp-iframe-holder .mfp-content {max-width:100%;width:100%;height:100%;}
                    .mfp-iframe-scaler iframe {background:#fff;padding:10px;box-sizing:border-box;box-shadow:none;}
                ');
                $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js');

                // Fancybox
                if (in_array($view, array('item', 'items', 'category', 'categories', 'user', 'users'))) {
                    $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css');
                    $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js');
                }

                // CSS
                if ($option == 'com_k2' || $adminModuleIncludes) {
                    $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
                }
                if ($option == 'com_k2') {
                    $document->addStyleSheet('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap');
                    $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.backend.css?v='.K2_CURRENT_VERSION.'&b='.K2_BUILD_ID);
                }
                if ($adminModuleIncludes) {
                    $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.global.css?v='.K2_CURRENT_VERSION.'&b='.K2_BUILD_ID);
                }
            }

            // Frontend only
            if ($app->isSite()) {
                // Magnific Popup
                if (!$user->guest || ($option == 'com_k2' && $view == 'item') || defined('K2_JOOMLA_MODAL_REQUIRED')) {
                    $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css');
                    $document->addStyleDeclaration('
                        /* K2 - Magnific Popup Overrides */
                        .mfp-iframe-holder {padding:10px;}
                        .mfp-iframe-holder .mfp-content {max-width:100%;width:100%;height:100%;}
                        .mfp-iframe-scaler iframe {background:#fff;padding:10px;box-sizing:border-box;box-shadow:none;}
                    ');
                    $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js');
                }

                // JS
                $document->addScript(JURI::root(true).'/media/k2/assets/js/k2.frontend.js?v='.K2_CURRENT_VERSION.'&b='.K2_BUILD_ID.'&sitepath='.JURI::root(true).'/');

                // Add related CSS to the <head>
                if ($params->get('enable_css')) {
                    jimport('joomla.filesystem.file');
                    $template = JRequest::getCmd('template');

                    // Simple Line Icons
                    $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.min.css');

                    // k2.css
                    if (isset($template) && JFile::exists(JPATH_SITE.'/templates/'.$template.'/css/k2.css')) {
                        $document->addStyleSheet(JURI::root(true).'/templates/'.$template.'/css/k2.css?v='.K2_CURRENT_VERSION);
                    } elseif (JFile::exists(JPATH_SITE.'/templates/'.$app->getTemplate().'/css/k2.css')) {
                        $document->addStyleSheet(JURI::root(true).'/templates/'.$app->getTemplate().'/css/k2.css?v='.K2_CURRENT_VERSION);
                    } else {
                        $document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.css?v='.K2_CURRENT_VERSION.'&b='.K2_BUILD_ID);
                    }

                    // k2.print.css
                    if (JRequest::getInt('print') == 1) {
                        if (isset($template) && JFile::exists(JPATH_SITE.'/templates/'.$template.'/css/k2.print.css')) {
                            $document->addStyleSheet(JURI::root(true).'/templates/'.$template.'/css/k2.print.css?v='.K2_CURRENT_VERSION, 'text/css', 'print');
                        } elseif (JFile::exists(JPATH_SITE.'/templates/'.$app->getTemplate().'/css/k2.print.css')) {
                            $document->addStyleSheet(JURI::root(true).'/templates/'.$app->getTemplate().'/css/k2.print.css?v='.K2_CURRENT_VERSION, 'text/css', 'print');
                        } else {
                            $document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.print.css?v='.K2_CURRENT_VERSION.'&b='.K2_BUILD_ID, 'text/css', 'print');
                        }
                    }
                }
            }
        }
    }
}
