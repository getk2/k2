<?php
/**
 * @version    2.10.x
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2020 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<?php if(isset($this->items) && count($this->items)): ?>
<ul class="liveSearchResults">
    <?php foreach($this->items as $item): ?>
    <li><a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
