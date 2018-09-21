<?php
/**
 * @version    2.9.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2018 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

$output = '';
if ($params->get('home')) {
    $output .= '<span class="bcTitle">'.JText::_('K2_YOU_ARE_HERE').'</span><a href="'.JURI::root().'">'.$params->get('home', JText::_('K2_HOME')).'</a>';
    if (count($path)) {
        foreach ($path as $link) {
            $output .= '<span class="bcSeparator">'.$params->get('seperator', '&raquo;').'</span>'.$link;
        }
    }
    if ($title) {
        $output .= '<span class="bcSeparator">'.$params->get('seperator', '&raquo;').'</span>'.$title;
    }
} else {
    if ($title) {
        $output .= '<span class="bcTitle">'.JText::_('K2_YOU_ARE_HERE').'</span>';
    }
    if (count($path)) {
        foreach ($path as $link) {
            $output .= $link.'<span class="bcSeparator">'.$params->get('seperator', '&raquo;').'</span>';
        }
    }
    $output .= $title;
}

?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2BreadcrumbsBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
    <?php echo $output; ?>
</div>
