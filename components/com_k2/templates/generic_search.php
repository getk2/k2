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

<?php if(isset($this->items) && count($this->items)): ?>
<ul class="liveSearchResults">
    <?php foreach($this->items as $item): ?>
    <li><a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
