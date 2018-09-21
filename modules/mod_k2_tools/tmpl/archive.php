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

?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2ArchivesBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
    <ul>
        <?php foreach ($months as $month): ?>
        <li>
            <a href="<?php echo $month->link; ?>">
                <?php echo $month->name.' '.$month->y; ?>
                <?php if ($params->get('archiveItemsCounter')) echo ' ('.$month->numOfItems.')'; ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
