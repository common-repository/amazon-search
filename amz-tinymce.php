<?php
/*
Description: Adds the Amazon buttons to the rich text editor and quicktag toolbar.
Author: Stephanie Leary
Author URI: http://www.sillybean.net
*/

/**
 * TinyMCE based on code from ZenphotoPress, copyright Alessandro "Simbul" Morandi
   Quicktag based on AddQuickTag by Roel Meurders, Frank B&uuml;ltge
 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
class amz_tinymce {
	
	/**
	 * Add the plugin for the tinyMCE editor
	 * @param $plugin_array	Plugins array
	 * @return	Updated plugins array
	 */
	function extended_editor_mce_plugins($plugin_array) {
		$plugin_array['amz_tinymce'] = get_bloginfo('wpurl') . '/' . PLUGINDIR . '/amazon-search/tinymce/editor_plugin.js';
		return $plugin_array;
	}
	
	/**
	 * Add the button for the tinyMCE editor
	 * @param buttons	Buttons array
	 * @return	Updated buttons array
	 */
	function extended_editor_mce_buttons($buttons) {
		array_push($buttons, 'separator', 'amz_tinymce');
    	return $buttons;
	}

	function extend_tinymce() {
		// Ckeck permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', array('amz_tinymce', 'extended_editor_mce_plugins'));
			add_filter('mce_buttons', array('amz_tinymce','extended_editor_mce_buttons'));
		}
	}
}

// Add actions
add_action('init', array('amz_tinymce','extend_tinymce'));

// only for post.php, page.php, post-new.php, page-new.php, comment.php
if (strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php') || strpos($_SERVER['REQUEST_URI'], 'page.php') || strpos($_SERVER['REQUEST_URI'], 'comment.php')) {
	add_action('admin_footer', 'wpaq_addsome');

	function wpaq_addsome() {
		
			echo '
				<script type="text/javascript">
					<!--
					if (wpaqToolbar = document.getElementById("ed_toolbar")) {
						var wpaqNr, wpaqBut, wpaqStart, wpaqEnd;
			
						wpaqStart = "[amazon ASIN=]";
						wpaqEnd = "[/amazon]";
						wpaqNr = edButtons.length;
						edButtons[wpaqNr] = new edButton(\'ed_wpaq_amazon\', \'' . 'Amazon' . '\', wpaqStart, wpaqEnd,\'\');
						var wpaqBut = wpaqToolbar.lastChild;
						while (wpaqBut.nodeType != 1) {
							wpaqBut = wpaqBut.previousSibling;
						}
						wpaqBut = wpaqBut.cloneNode(true);
						wpaqToolbar.appendChild(wpaqBut);
						wpaqBut.value = \'' . 'Amazon' . '\';
						wpaqBut.title = "Insert Amazon link";
						wpaqBut.onclick = function () {edInsertTag(edCanvas, wpaqNr);}
						wpaqBut.id = "ed_wpaq_amazon";
					}
					//-->
				</script>
				';
		
	} //End wpaq_addsome
} // End if
?>
