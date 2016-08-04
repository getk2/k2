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
<input type="checkbox" class="edit-metalic" name="metalic[]" id="edit-metalic-1" value="90,92,93,150,151,91,152">
<label class="option" for="edit-metalic-1">Name category1</label><br>

<input type="checkbox" class="edit-metalic" name="metalic[]" id="edit-metalic-2" value="104,153,154,155,156,157,158">
<label class="option" for="edit-metalic-2">Name category2</label><br>

<input type="checkbox" class="edit-metalic" name="metalic[]" id="edit-metalic-3" value="108,159,160,161,162,163,164">
<label class="option" for="edit-metalic-3">Name category2</label><br>
<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2CalendarBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
	<?php echo $calendar; ?>
	<div class="clr"></div>
</div>
