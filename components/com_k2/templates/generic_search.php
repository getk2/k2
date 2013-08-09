<?php
/**
 * @version		$Id: generic_search.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<?php if(count($this->items)): ?>
<ul class="liveSearchResults">
	<?php foreach($this->items as $item): ?>
	<li><a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a></li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
