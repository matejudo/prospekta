<?php

class TextEditor
{
	public function getHTML($name, $content = null)
	{
		$html = '
			<script language="javascript" type="text/javascript">
				tinyMCE.init({
					mode : "textareas",
					theme : "advanced",
					plugins : "pagebreak,table,contextmenu,media", 
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,formatselect,separator,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink,image,removeformat,separator,code,separator,pagebreak",
					theme_advanced_buttons2 : "tablecontrols,separator,media",
					theme_advanced_buttons3 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,
					force_p_newlines : true,
					convert_newlines_to_brs : false,
					force_br_newlines : false,
					dialog_type : "modal",
					theme_advanced_resize_horizontal : false,
					theme_advanced_blockformats : "p,h2,h3,h4,blockquote,code",
					content_css : "/prospekta/public/styles/master.css",
					extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]"
				});
			</script>
			<textarea name="' . $name . '" style="width: 100%;" rows="15" id="text">' . $content . '</textarea>
		';
		
		return $html;
	}
}