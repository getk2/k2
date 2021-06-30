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

?>

<?php if (count($selectedTags)): ?>
<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2SelectedTagsBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
    <ul>
        <?php foreach ($selectedTags as $key => $tag): ?>
        <?php if ($selectedTagsLimit > 0 && ($key + 1) > $selectedTagsLimit) break; ?>
        <li>
            <a href="<?php echo JRoute::_(K2HelperRoute::getTagRoute(urldecode($tag))); ?>"><?php echo urldecode($tag); ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
