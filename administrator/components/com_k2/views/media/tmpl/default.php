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
<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$('#elfinder').elfinder({
				url: '<?php echo JURI::base(true); ?>/index.php?option=com_k2&view=media&task=connector',
				customData: {
					'<?php echo $this->token; ?>': 1
				},
				<?php if($this->mimes): ?>
				onlyMimes: [<?php echo $this->mimes; ?>],
				<?php endif; ?>
				<?php if($this->fieldID): ?>
				getFileCallback: function(image) {
					var basePath = '<?php echo JURI::root(true); ?>';
					var imgPath = image.path;
					var newImgPath = imgPath.replace(basePath, '');
					parent.elFinderUpdate('<?php echo $this->fieldID; ?>', newImgPath);
				}
				<?php else: ?>
				height: 600
				<?php endif; ?>
			});
		});
	})(jQuery);
</script>
<div id="elfinderContainer">
	<div id="elfinder"></div>
</div>
