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
