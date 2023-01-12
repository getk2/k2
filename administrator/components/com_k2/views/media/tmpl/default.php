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
                },
                height: (window.innerHeight) * 0.96
                <?php else: ?>
                height: (window.innerHeight) - <?php if(K2_JVERSION == '30'): ?>126<?php else: ?>160<?php endif; ?>
                <?php endif; ?>
            });
        });
    })(jQuery);
</script>
<div id="elfinderContainer">
    <div id="elfinder"></div>
</div>
