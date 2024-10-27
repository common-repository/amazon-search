<?php
/*
Plugin Name: Amazon Search
Plugin URI: http://wordpress.org/extend/plugins/amazon-search/
Description: Easily add links to Amazon products with your Amazon Associate ids using a special markup. Optionally includes a search widget to allow searching of any international Amazon site and displays the results right in your blog.
Author: Andy Maloney
Version: 1.2.0
Author URI: http://imol.gotdns.com/
*/

global $gAMZ_Tools;


require_once( 'amz-ip2Nation.php' );


if (!class_exists('AMZ_Tools'))
{
	class AMZ_Tools
	{
		var $_version = '1.2.0';
		
		var $_adminOptionsName = 'AMZ_SearchAdminOptions';
		var $_db_table;
		
		var $_pluginURL;

		var $_linkRegEx = '@\[amazon ([^\]]*)\](.+(?!\[/amazon\]).+)\[/amazon\]@iU';
		
		var $_ip2Nation;	// our ip2Nation handling class
		var $_search;		// our search handling class

		
		function AMZ_Tools()
		{ 
			global $wpdb;

			$this->_db_table = $wpdb->prefix . "amz_servers";
			$this->_pluginURL = get_option('siteurl') . '/wp-content/plugins/amazon-search';
			$this->_ip2Nation = new AMZ_ip2nation;
		} 
		
		function getSearch()
		{
			if (!isset($this->_search))
			{
				require_once( 'amz-searchBox.php' );
				$this->_search = new AMZ_Search;
			}
			
			return $this->_search;
		}
		
		function isCURLinstalled()
		{
			return function_exists( 'curl_init' );
		}
		
		function addHeaderCode()
		{
			echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/amazon-search/css/default.css" />' . "\n";
		
			// check for CSS file specific to this theme
			$template = get_option('template');
			
			if ( $template !== "default" )
			{
				$theme_css = getcwd() . '/wp-content/plugins/amazon-search/css/' . $template . '.css';
				
				if ( is_readable( $theme_css ) )
					echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/amazon-search/css/' . $template . '.css" />' . "\n";
			}			
		}

		function db_install()
		{
			global $wpdb;
		
			$table_name = $this->_db_table;
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
			{
				$sql = "CREATE TABLE {$table_name} (
					  id enum('com','ca','uk','fr','de','jp') NOT NULL default 'com',
					  server enum('amazon.com','amazon.ca','amazon.co.uk','amazon.fr','amazon.de','amazon.co.jp') NOT NULL default 'amazon.com',
					  assoc_server enum('affiliate-program.amazon.com','associates.amazon.ca','affiliate-program.amazon.co.uk','partenaires.amazon.fr','partnernet.amazon.de','affiliate.amazon.co.jp') NOT NULL default 'affiliate-program.amazon.com',
					  default_tag enum('imol-20','imol0a-20','imol-21','imol08-21','imol05-21','imol-22') NOT NULL default 'imol-20',
					  user_tag varchar(32) default NULL,
					  PRIMARY KEY  (id),
					  UNIQUE KEY server (server)
					) PACK_KEYS=1
					";
		
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
		
				$_DATA[] = "INSERT IGNORE INTO {$table_name} VALUES ('com', 'amazon.com', 'affiliate-program.amazon.com', 'imol-20', '')";
				$_DATA[] = "INSERT IGNORE INTO {$table_name} VALUES ('ca', 'amazon.ca', 'associates.amazon.ca', 'imol0a-20', '')";
				$_DATA[] = "INSERT IGNORE INTO {$table_name} VALUES ('uk', 'amazon.co.uk', 'affiliate-program.amazon.co.uk', 'imol-21', '')";
				$_DATA[] = "INSERT IGNORE INTO {$table_name} VALUES ('fr', 'amazon.fr', 'partenaires.amazon.fr', 'imol08-21', '')";
				$_DATA[] = "INSERT IGNORE INTO {$table_name} VALUES ('de', 'amazon.de', 'partnernet.amazon.de', 'imol05-21', '')";
				$_DATA[] = "INSERT IGNORE INTO {$table_name} VALUES ('jp', 'amazon.co.jp', 'affiliate.amazon.co.jp', 'imol-22', '')";
		
		    	foreach ($_DATA as $insert)
		    	{
					$results = $wpdb->query( $insert );
				}
				
				add_option("AMZ_db_version", $this->_version);
		   }
		}

		function getAdminOptions()
		{			
			$adminOptions = array(
				'contribute' => '4', 
				'default_category' => 'Blended',  
				'default_search' => 'WordPress',  
				'default_server' => 'com',
				'image_size' => 'Medium',
				'search_count' => '0',
				'xsl' => $this->_pluginURL . '/xsl/wp-amz-search.xsl',
				'use_text_links' => true,
				'use_ip2nation' => true,
				'use_searching' => true,
				'use_tinymce' => true,
				);
				
			$savedOptions = get_option($this->_adminOptionsName);
			
			$dirty = false;
			
			if (!empty($savedOptions))
			{ 
				foreach ($savedOptions as $key => $option)
				{
					if ( $adminOptions[$key] != $option )
					{
						$dirty = true;
						
						$adminOptions[$key] = $option;
					}
				}
			}             
		
			if ( $dirty )
				update_option($this->_adminOptionsName, $adminOptions);
			
			if ( !$this->isCURLinstalled() )
				$adminOptions['use_searching'] = false;
		
			return $adminOptions; 
		}

		function getServerInfo( $serverID )
		{
			global $wpdb;

			$_AMZ_PREFS = $this->getAdminOptions();

			// Get the server info
			$sql = 'SELECT * FROM ' . $this->_db_table . ' WHERE id="' . $serverID .'"';
			$server_info = $wpdb->get_row($sql, ARRAY_A);
			
			if ( $server_info['user_tag'] == NULL )
			{
				$server_info['user_tag'] = $server_info['default_tag'];
			}
			else if ( $_AMZ_PREFS['contribute'] > 0 )
			{
				$_AMZ_PREFS['search_count'] += 1;
			
				if ( $_AMZ_PREFS['search_count'] >= $_AMZ_PREFS['contribute'] )
				{
					$server_info['user_tag'] = $server_info['default_tag'];
					$_AMZ_PREFS['search_count'] = 0;
				}
				
				// Update the search count
				update_option($this->_adminOptionsName, $_AMZ_PREFS); 
			}
			
			return $server_info;
		}
		
		function printAdminPage()
		{			
			global $wpdb;

			$display = '';
			
			$_AMZ_PREFS = $this->getAdminOptions();

			// Check for and save updated prefs
			if ( isset( $_POST['Save'] ) )
			{
				check_admin_referer('amz_search-admin_options');
				
				$new_prefs = $_POST['new_prefs'];
	
				// workaround for HTML being braindead - it only passes checkboxes which are 'on'
				$_AMZ_PREFS['use_text_links'] = false;
				$_AMZ_PREFS['use_ip2nation'] = false;
				$_AMZ_PREFS['use_searching'] = false;
				$_AMZ_PREFS['use_tinymce'] = false;

				foreach ( $new_prefs as $pref => $value )
				{
					if (isset($new_prefs[$pref]))
					{
						$_AMZ_PREFS[$pref] = $new_prefs[$pref];
					}
				}
			
				if ( !$this->isCURLinstalled() )
					$_AMZ_PREFS['use_searching'] = false;
							
				update_option($this->_adminOptionsName, $_AMZ_PREFS);

				$results = $wpdb->get_results('SELECT * FROM ' . $this->_db_table, ARRAY_A);
				foreach ($results as $server_info)
				{
					$associate_id = trim( $_POST['usertag'][$server_info['id']] );
					$sql = 'UPDATE ' . $this->_db_table . " SET user_tag='$associate_id' WHERE id='{$server_info['id']}'";
					$wpdb->query($sql);
				}
				
				$display .= '<div class="updated"><p><strong>Settings Updated.</strong></p></div>';
			}

			$display .= '<form method="POST" action="' . $_SERVER["REQUEST_URI"] . '">';
			
			$display .= $this->_nonce_field('amz_search-admin_options');

			// Submit
			$display .= '<div style="border:#C0C0C0 dotted 1px; padding: 5px; text-align: right;">
				Amazon Search v' . $this->_version . ' by <a href="http://imol.gotdns.com">Andy Maloney</a> <input type=submit name=Save value="Save"/>
				</div>';
									
			// Amazon Associate prefs
			$display .= '<div style="border:#C0C0C0 dotted 1px; padding: 10px;">
				<u>Associate IDs</u><br/>
				<table cellspacing=5 width="100%">
				<tr style="font-weight: bold;">
				<td align=center>Default</td>
				<td colspan="2">Amazon Server</td>
				<td>Associate ID</td>
				<td>Web Site</td>
				</tr>';
			
			$results = $wpdb->get_results('SELECT * FROM ' . $this->_db_table, ARRAY_A);
			foreach ($results as $server_info)
			{
				$flag_url = $this->_pluginURL . '/images/flag_' . $server_info['id'] . '.gif';
				$alt_flag = $server_info['id'] . ' flag';
			
				$display .= '<tr>';
				
				$display .= '<td width=70 align=center>';
				$display .= '<input type=radio name="new_prefs[default_server]"';
				$display .= ($_AMZ_PREFS['default_server'] == $server_info['id'] ? ' checked' : '' );
				$display .= ' value="' . $server_info['id'] . '"/>';
				$display .= '</td>';
				
				$display .= '<td width=30><img src="' . $flag_url . '" width=24 height=13 alt="' . $alt_flag . '"/></td>';
				
				$display .= '<td width=100>' . $server_info['server'] . '</td>';
				
				$display .= '<td><input type=text name="usertag[' . $server_info['id'] . ']"';
				$display .= ' size=24 value="' . $server_info['user_tag'] . '"/></td>';
			
				$display .= '<td><a href="https://' . $server_info['assoc_server'] . '" target="_blank">';
				$display .= $server_info['assoc_server'] . '</a></td>';
				
				$display .= '</tr>';
			}
			
			$display .= '</table></div>';
			
			// Contribute to Andy prefs
			$display .= '<div style="border:#C0C0C0 dotted 1px; padding: 10px 10px 0px 10px;">
				<u>Development Contribution</u><p>
				Please help support development of this plugin by using Andy\'s associate ids for some of your searches.<br/>
				<p>Use Andy\'s associate id every
				<select name="new_prefs[contribute]">';
			
			$values = array( 0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 25, 50 );
			
			foreach ( $values as $value )
			{
				$display .= '<option' . ($_AMZ_PREFS['contribute'] == $value ? ' selected' : '' ) . ">$value</option>";
			}
			
			$display .= '</select> searches [or page displays if you are using text links - see below].</div>';

			// Text links prefs
			$display .= '<div style="border:#C0C0C0 dotted 1px; padding: 10px;">
				<u>Text Links</u><br/>
				<table cellspacing=8 width="100%">';

			$display .= '<tr><td align=right>Use Text Links</td>';
			$display .= '<td><input type=checkbox name="new_prefs[use_text_links]"' . ($_AMZ_PREFS['use_text_links'] ? ' checked' : '' ) . '/></td>';
			$display .= '<td>Allows you to use tags of the form [amazon ISBN=###]Title[/amazon] in your posts. 
							<br/>These will be converted to Amazon links using your associate IDs from above.</td>';
			$display .= '</tr>';

			if ( $this->_ip2Nation->isDBinstalled() )
			{
				$display .= '<tr><td align=right>Use ip2Nation</td>';
				$display .= '<td><input type=checkbox name="new_prefs[use_ip2nation]"' . ($_AMZ_PREFS['use_ip2nation'] ? ' checked' : '' ) . '/></td>';
				$display .= '<td>Use the <a href="http://www.ip2nation.com/ip2nation" target="_blank">ip2nation database</a> to look up the visitor\'s IP and use the best Amazon store based on location and language.</td>';
				$display .= '</tr>';
			}
			else
			{
				$display .= '<tr><td align=right style="color: gray;">Use ip2Nation</td><td></td>';
				$display .= '<td>If you install the <a href="http://www.ip2nation.com/ip2nation" target="_blank">ip2nation database</a>, we can use geolocation on the visitor\'s IP to pick the best Amazon store based on location and language.</td>';
				$display .= '</tr>';
			}
			
			$display .= '<tr><td align=right>Add Editor Buttons</td>';
			$display .= '<td><input type=checkbox name="new_prefs[use_tinymce]"' . ($_AMZ_PREFS['use_tinymce'] ? ' checked' : '' ) . '/></td>';
			$display .= '<td>Allows you to turn off the Amazon buttons in the TinyMCE editor and quicktag toolbar.</td>';
			$display .= '</tr>';
			
			$display .= '</table></div>';
			
			// Search prefs
			$display .= '<div style="border:#C0C0C0 dotted 1px; padding: 10px;">
				<u>Searching</u>';

			if ( !$this->isCURLinstalled() )
			{
				$display .= '<div style="padding-left: 10px; padding-top: 10px;" class="attention">Your HTTP server does not support cURL.  The search facility of this plugin will not work and has been disabled.  Text links will not be affected.</div>';
			}
			
			$display .= '<table cellspacing=8 width="100%">';
			$display .= '<tr><td align=right>Use Searching</td>';
			$display .= '<td><input type=checkbox name="new_prefs[use_searching]"' . ($_AMZ_PREFS['use_searching'] ? ' checked' : '' ) . '/></td>';
			$display .= '<td>Allows you to turn off the search capability and widget so you just use text links.</td>';
			$display .= '</tr>';
			
			$display .= '<tr><td align=right>Default Category</td><td>';

			$display .= $this->buildCategoryOptionList('new_prefs[default_category]', $_AMZ_PREFS['default_category']);
			$display .= '</td>';
			$display .= '</tr>';
			
			$display .= '<tr><td align=right>Default Search Term</td>';
			$display .= '<td><input type=text name="new_prefs[default_search]" size=48 value="' . $_AMZ_PREFS['default_search'] . '"/></td>';
			$display .= '</tr>';
			
			$display .= '<tr><td align=right>XSL file [full URL]</td>';
			$display .= '<td><input type=text name="new_prefs[xsl]" size=48 value="' . $_AMZ_PREFS['xsl'] . '"/></td>';
			$display .= '</tr>';
			
			$display .= '<tr><td align=right>Image Size</td>';
			$display .= '<td><select name="new_prefs[image_size]">';
			$display .= '<option' . ($_AMZ_PREFS['image_size'] == 'None' ? ' selected' : '' ) . ">None</option>";
			$display .= '<option' . ($_AMZ_PREFS['image_size'] == 'Small' ? ' selected' : '' ) . ">Small</option>";
			$display .= '<option' . ($_AMZ_PREFS['image_size'] == 'Medium' ? ' selected' : '' ) . ">Medium</option>";
			$display .= '<option' . ($_AMZ_PREFS['image_size'] == 'Large' ? ' selected' : '' ) . ">Large</option>";
			$display .= '</select></td>';
			$display .= '</tr>';
			
			$display .= '</table></div>';
			
			// Submit
			$display .= '<div style="border:#C0C0C0 dotted 1px; padding: 5px; text-align: right;">
				[<a href="http://imol.gotdns.com">Andy\'s site</a>] <input type=submit name=Save value="Save"/>
				</div>
				</form>';

			echo $display;
		}
		
		// my own nonce functions because I don't want to echo the stuff immediately...
		function _nonce_field($action = -1, $name = "_wpnonce", $referer = true)
		{
			$name = attribute_escape($name);
			$ret = '<input type="hidden" name="' . $name . '" value="' . wp_create_nonce($action) . '" />';
			if ( $referer )
				$ret .= $this->_referer_field();
				
			return $ret;
		}
		
		function _referer_field()
		{
			$ref = attribute_escape($_SERVER['REQUEST_URI']);
			$ret = '<input type="hidden" name="_wp_http_referer" value="'. $ref . '" />';
			if ( wp_get_original_referer() )
			{
				$original_ref = attribute_escape(stripslashes(wp_get_original_referer()));
				$ret .= '<input type="hidden" name="_wp_original_http_referer" value="'. $original_ref . '" />';
			}
			
			return $ret;
		}
		
		function buildCategoryOptionList( $name, $selected )
		{
			$category = '<select name="' . $name . '" class="category-select">';
			$category .= '<option ' . ($selected == 'Blended' ? 'SELECTED ' : '' ) . 'value="Blended">All</option>';
			$category .= '<option ' . ($selected == 'Books' ? 'SELECTED ' : '' ) . 'value="Books">Books</option>';
			$category .= '<option ' . ($selected == 'DVD' ? 'SELECTED ' : '' ) . 'value="DVD">DVDs</option>';
			$category .= '<option ' . ($selected == 'Music' ? 'SELECTED ' : '' ) . 'value="Music">Music</option>';
			$category .= '<option ' . ($selected == 'Software' ? 'SELECTED ' : '' ) . 'value="Software">Software&nbsp;&nbsp;</option>';
			$category .= '</select>';
			
			return $category;
		}
		
		// Convert a string of the form "a=b c=d" into an associative array
		//	From here: <http://www.php.net/manual/en/function.split.php>
		function parseNameValues($text)
		{
			$values = array();
			
			if (preg_match_all('/([^=\s]+)=("(?P<value1>[^"]+)"|' . '\'(?P<value2>[^\']+)\'|(?P<value3>.+?)\b)/', $text, $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
					$values[strtolower(trim($match[1]))] = trim(@$match['value1'] . @$match['value2'] . @$match['value3']);
			}
			
			return $values;
		}

		// Links are of the form: [amazon isbn=0123456789]Title[/amazon]
		//	You may also use asin in place of isbn.
		//	These will be converted to amazon links pointing at your default server using your associate ID for that server.
		function createLinks( $content )
		{			
			if ( preg_match_all($this->_linkRegEx, $content, $matches) )
			{
				$_AMZ_PREFS = $this->getAdminOptions();
				
				$server_code = $_AMZ_PREFS['default_server'];

				if ( $_AMZ_PREFS['use_ip2nation'] )
					$server_code = $this->_ip2Nation->getAmazonServerFromIP( $server_code );
					
				$server_info = $this->getServerInfo( $server_code );
				$amazon_server = $server_info['server'];
					
				$ass_id = $server_info['user_tag'];
				
				for ($i=0; $i < count($matches[0]); $i++)
				{
					$match = $matches[0][$i];	// the whole text that was matched
					$args = strip_tags( trim($matches[1][$i]) );	// of the form "a=b c=d"
					$title = $matches[2][$i];	// the title of the link
					
					if (empty($args) || empty($title))
						continue;
						
					$nameValues = $this->parseNameValues( $args );
					$asin = $nameValues['asin'];
					$isbn = $nameValues['isbn'];
					
					// check if we should use ISBN
					if ( empty($asin) )
						$asin = $isbn;
						
					if ( empty($asin) )
						continue;
						
					$newlink = '<a href="http://www.' . $amazon_server . '/dp/' . strtoupper($asin) . '/?tag=' . $ass_id . '" rel="nofollow" target="_blank">' . $title . '</a>';
					
					$content = str_replace($match, $newlink, $content);
				}
			}
			
			return $content;
		}
	}
}

// Initialize the admin panel
function AMZ_Search_adminPage()
{
	global $gAMZ_Tools;
	
	if (!isset($gAMZ_Tools))
		return;
	
	if (function_exists('add_options_page'))
		add_options_page('Amazon Search', 'Amazon Search', 9, basename(__FILE__), array(&$gAMZ_Tools, 'printAdminPage'));
}


// Handle all our main registrations and actions
if (class_exists('AMZ_Tools'))
{
	$gAMZ_Tools = new AMZ_Tools();

	register_activation_hook( __FILE__, array(&$gAMZ_Tools, 'db_install') );

	$_AMZ_PREFS = $gAMZ_Tools->getAdminOptions();

	// add handling of [amazon][/amazon] links
	if ( $_AMZ_PREFS['use_text_links'] )
		add_filter('the_content', array(&$gAMZ_Tools, 'createLinks'), 1);
		
	add_action('wp_head', array(&$gAMZ_Tools, 'addHeaderCode'), 1);
	
	add_action('admin_menu', 'AMZ_Search_adminPage');

	if ( $_AMZ_PREFS['use_tinymce'] )
		require_once( 'amz-tinymce.php' );

	if ( $_AMZ_PREFS['use_searching'] )
		$gAMZ_Tools->getSearch()->addActions();
}

?>