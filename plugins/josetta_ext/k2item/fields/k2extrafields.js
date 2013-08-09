/**
 * @version		$Id: k2extrafields.js 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

$K2(document).ready(function() {
	extraFields();
	setTimeout(function() {
		initExtraFieldsEditor();
	}, 1000);

	$K2('[id$=josetta_form_catid]').change(function() {
		if ($K2(this).find('option:selected').attr('disabled')) {
			alert(K2Language[4]);
			$K2(this).val('0');
			return;
		}
		extraFields();
	});
});

function extraFields() {
	var selectedValue = $K2('[id$=josetta_form_catid]').val();
	var url = K2BasePath + '/index.php?option=com_k2&view=item&task=extraFields&cid=' + selectedValue + '&id=' + Josetta.josettaItemid;
	$K2('#extraFieldsContainer').fadeOut('slow', function() {
		$K2.ajax({
			url : url,
			type : 'get',
			success : function(response) {
				$K2('#extraFieldsContainer').html(response);
				initExtraFieldsEditor();
				$K2('img.calendar').each(function() {
					inputFieldID = $K2(this).prev().attr('id');
					imgFieldID = $K2(this).attr('id');
					Calendar.setup({
						inputField : inputFieldID,
						ifFormat : "%Y-%m-%d",
						button : imgFieldID,
						align : "Tl",
						singleClick : true
					});
				});
				$K2('#extraFieldsContainer').fadeIn('slow');
			}
		});
	});
}

function initExtraFieldsEditor() {
	$K2('.k2ExtraFieldEditor').each(function() {
		var id = $K2(this).attr('id');
		if ( typeof tinymce != 'undefined') {
			if (tinyMCE.get(id)) {
				tinymce.EditorManager.remove(tinyMCE.get(id));
			}
			tinyMCE.execCommand('mceAddControl', false, id);
		} else {
			new nicEditor({
				fullPanel : true,
				maxHeight : 180,
				iconsPath : K2BasePath + '/media/k2/assets/images/system/nicEditorIcons.gif'
			}).panelInstance($K2(this).attr('id'));
		}
	});
}

function syncExtraFieldsEditor() {
	$K2('.k2ExtraFieldEditor').each(function() {
		editor = nicEditors.findEditor($K2(this).attr('id'));
		if ( typeof editor != 'undefined') {
			if (editor.content == '<br>' || editor.content == '<br />') {
				editor.setContent('');
			}
			editor.saveContent();
		}
	});
}