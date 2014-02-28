/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

$K2(document).ready(function() {

  // Generic function to get URL params passed in .js script include
	function getUrlParams(targetScript, varName) {
		var scripts = document.getElementsByTagName('script');
		var scriptCount = scripts.length;
		for (var a = 0; a < scriptCount; a++) {
			var scriptSrc = scripts[a].src;
			if (scriptSrc.indexOf(targetScript) >= 0) {
				varName = varName.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
				var re = new RegExp("[\\?&]" + varName + "=([^&#]*)");
				var parsedVariables = re.exec(scriptSrc);
				if (parsedVariables !== null) {
					return parsedVariables[1];
				}
			}
		}
	}

	// Set the site root path
	var K2SitePath = getUrlParams('k2.js', 'sitepath');

  $K2('.tagRemove').click(function(event) {
    event.preventDefault();
    $K2(this).parent().remove();
  });

  /*
  $K2('ul.tags').click(function() {
    //$K2('#search-field').focus();
  });
  */

  $K2('.k2-search-field').keypress(function(event) {
    if (event.which == '13') {
      if ($K2(this).val() != '') {
        $K2('<li id="' + this.attributes['rel'].value + '_tagAdd" class="addedTag">' + $K2(this).val() + '<span class="tagRemove" onclick="Josetta.itemChanged('+this.id+');$K2(this).parent().remove();">x</span><input type="hidden" value="' + $K2(this).val() + '" name="'+this.attributes['rel'].value + '[tags][]"></li>').insertBefore('.tags #' + this.attributes['rel'].value + '_tagAdd.tagAdd');
        Josetta.itemChanged(this);
        $K2(this).val('');
      }
    }
  });

  $K2('.k2-search-field').autocomplete({
    source : function(request, response) {
      var target = this.element[0];
      $K2.ajax({
        type : 'post',
        url : K2SitePath + 'index.php?option=com_k2&view=item&task=tags',
        data : 'q=' + request.term,
        dataType : 'json',
        success : function(data) {
          target.removeClass('tagsLoading');
          response($K2.map(data, function(item) {
            return item;
          }));
        }
      });
    },
    minLength : 3,
    select : function(event, ui) {
      $K2('<li id="' + this.attributes['rel'].value + '_tagAdd" class="addedTag">' + ui.item.label + '<span class="tagRemove" onclick="Josetta.itemChanged('+this.id+');$K2(this).parent().remove();">x</span><input type="hidden" value="' + ui.item.value + '" name="'+this.attributes['rel'].value + '[tags][]"></li>').insertBefore('.tags #' + this.attributes['rel'].value + '_tagAdd.tagAdd');
      Josetta.itemChanged(this);
      this.value = '';
      return false;
    },
    search : function(event, ui) {
      event.target.addClass('tagsLoading');
    }
  });

});