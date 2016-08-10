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
<script type="text/javascript">
	$K2(document).ready(function() {
		var tokenKey = '<?php echo JSession::getFormToken(); ?>';
		$K2('#elfinder').elfinder({
			url : '<?php echo JURI::base(true); ?>/index.php?option=com_k2&view=media&task=connector',
			<?php if($this->mimes): ?>
			onlyMimes: [<?php echo $this->mimes; ?>],
			<?php endif; ?>
			<?php if($this->fieldID): ?>
			getFileCallback: function(image) {
				var basePath = '<?php echo JURI::root(true); ?>';
				var imgPath = image.path;
				var newImgPath = imgPath.replace(basePath, '');
				parent.elFinderUpdate('<?php echo $this->fieldID; ?>', newImgPath);
			},
			<?php else: ?>
			height: 600,
			<?php endif; ?>
		    handlers: {
		        upload: function(e, fm) {
		            if (e.data && e.data.csrftoken) {
		                fm.customData[tokenKey] = e.data.csrftoken;
		            }
		        }
		    }
		});
	});
</script>
<div id="elfinderContainer">
	<div id="elfinder"></div>
</div>
