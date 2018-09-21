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

/*
 * Important note for template overrides:
 * If you wish to use the live search option, you MUST maintain
 * the same class names for wrapping elements, e.g. the wrapping div and form.
*/

?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2SearchBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); if($params->get('liveSearch')) echo ' k2LiveSearchBlock'; ?>">
    <form action="<?php echo $action; ?>" method="get" autocomplete="off" class="k2SearchBlockForm">
        <input type="text" value="<?php echo $text; ?>" name="searchword" class="inputbox" onblur="if(this.value=='') this.value='<?php echo $text; ?>';" onfocus="if(this.value=='<?php echo $text; ?>') this.value='';" />

        <?php if($button): ?>
        <?php if($imagebutton): ?>
        <input type="image" alt="<?php echo $button_text; ?>" class="button" onclick="this.form.searchword.focus();" src="<?php echo JURI::base(true); ?>/components/com_k2/images/search.png" />
        <?php else: ?>
        <input type="submit" value="<?php echo $button_text; ?>" class="button" onclick="this.form.searchword.focus();" />
        <?php endif; ?>
        <?php endif; ?>

        <?php if($categoryFilter): ?>
        <input type="hidden" name="categories" value="<?php echo $categoryFilter; ?>" />
        <?php endif; ?>

        <?php if(!$app->getCfg('sef')): ?>
        <input type="hidden" name="option" value="com_k2" />
        <input type="hidden" name="view" value="itemlist" />
        <input type="hidden" name="task" value="search" />
        <?php endif; ?>

        <?php if($params->get('liveSearch')): ?>
        <input type="hidden" name="format" value="html" />
        <input type="hidden" name="t" value="" />
        <input type="hidden" name="tpl" value="search" />
        <?php endif; ?>

        <?php if($searchItemId): ?>
        <input type="hidden" name="Itemid" value="<?php echo $searchItemId;?>" />
        <?php endif; ?>
    </form>

    <?php if($params->get('liveSearch')): ?>
    <div class="k2LiveSearchResults"></div>
    <?php endif; ?>
</div>
