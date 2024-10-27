<?php

if (!class_exists('AMZ_Search'))
{
	class AMZ_Search
	{
		var $_cleanData;

		function addActions()
		{
			if (function_exists( 'curl_init' ) )
			{
				if ( isset($_REQUEST['amz_search']) )
					add_action('template_redirect', 'AMZ_Search_results');
		
				add_action('widgets_init','AMZ_Search_initWidgets');
			}
		}

		function printSearchBox()
		{			
			$this->storeCleanRequestData();
			$clean = $this->_cleanData;
			
			$display = '<div class="amz-search-widget">';
			$display .= '<form method="get" action="">';	
			$display .= '<span class=server>';
			$display .= 'amazon. ';
				
			$display .= $this->_buildServerOptionList( 'a_server', $clean['a_server'] );
			$display .= '</span>';
			
			$keyword = ( $clean['no_keyword'] ) ? '' : $clean['keyword'];
			$display .= '<p><input type="text" name="field-keywords" size="17" value="' . $keyword . '" class="keyword-input"/>';
		
			global $gAMZ_Tools;
			
			$display .= '<input type="image" value="Go" name="amz_search" '
						. 'src="' . $gAMZ_Tools->_pluginURL . '/images/amazon-go.gif" '
						. ' class="go"/>';
			$display .= '</form>';
			$display .= '<input type="hidden" name="category" value="' . $clean['category'] . '"/>';
			$display .= '</div>';
				
			echo $display;
		}

		function storeCleanRequestData()
		{	
			global $gAMZ_Tools;
			
			$_AMZ_PREFS = $gAMZ_Tools->getAdminOptions();
		
			// default and default default server
			if ( !empty( $_REQUEST['a_server'] ) )
				$a_server = substr( $_REQUEST['a_server'], 0, 3 );
			else
				$a_server = $_AMZ_PREFS['default_server'];
			
			$clean['a_server'] = $this->_validServer( $a_server );
			
			// default and default default category
			if ( !empty( $_REQUEST['category'] ) )
				$category = $_REQUEST['category'];
			else
				$category = $_AMZ_PREFS['default_category'];
			
			$clean['category'] = $this->_validCategory( $category );
			
			// default and default default keyword
			$clean['no_keyword'] = true;
			$keyword = '';
			
			if ( !empty( $_REQUEST['field-keywords'] ) )
			{
				$keyword = $_REQUEST['field-keywords'];
				
				$clean['no_keyword'] = false;
			}
						
			$clean['keyword'] = $this->_validKeyword( $keyword );
			
			if ( empty( $clean['keyword'] ) )
			{
				if ( !empty( $_AMZ_PREFS['default_search']) )
					$clean['keyword'] = $this->_validKeyword( $_AMZ_PREFS['default_search'] );
			
				if ( empty( $clean['keyword'] ) )
					$clean['keyword'] = 'Kahlil Gibran';
			
				$clean['no_keyword'] = true;
			}
			
			// which page of results
			if ( !empty( $_REQUEST['ItemPage'] ) )
			{
				$ItemPage = $_REQUEST['ItemPage'];
				settype( $ItemPage, 'integer' );
			}
			else
			{
				$ItemPage = 1;
			}
				
			$clean['item_page'] = $ItemPage;
			
			if ( $clean['a_server'] == 'com' )
				$clean['xml_server'] = 'us';
			else
				$clean['xml_server'] = $clean['a_server'];
									
			$this->_cleanData = $clean;
		}
		
		function getSearchResults()
		{	
			global $gAMZ_Tools;

			$_AMZ_PREFS = $gAMZ_Tools->getAdminOptions();
			$clean = $this->_cleanData;
	
			$server_info = $gAMZ_Tools->getServerInfo( $clean['a_server'] );
		
			$amazon_server = $server_info['server'];
			$ass_id = $server_info['user_tag'];
			$flag = $this->_pluginURL . '/images/flag_' . $server_info['id'] . '.gif';
	
			$display = '<div class="amz-search">';
			$display .= '<form method="get" action="' . $_SERVER['SCRIPT_NAME'] . '">';
			$display .= '<span class="server">';
			$display .= 'Search amazon. ';
			$display .= $this->_buildServerOptionList( 'a_server', $clean['a_server'] );
			$display .= $gAMZ_Tools->buildCategoryOptionList( 'category', $clean['category'] );
			$display .= '</span>';
			
			$display .= '<p><input type=text name="field-keywords" size=30 value="' . $clean['keyword'] . '"  class="keyword-input"/>';
			$display .= '<input type="image" value="Go" name="amz_search" '
					. 'src="' . $gAMZ_Tools->_pluginURL . '/images/amazon-go.gif"'
					. ' class="go"/>';
			
			$display .= '</form>';
			
			$display .= '<hr/>';
			
			if ( $clean['no_keyword'] )
				$display .= "<p>You did not specify a valid search term, so here is a search for '{$clean['keyword']}'.<br/>";
				
			$amazon_search_url = "http://xml-{$clean['xml_server']}.amznxslt.com/onca/xml?";
			
			// First the amazon vars...
			$amazon_search_url .= "Service=AWSECommerceService&SubscriptionId=1M17J4TZNSDYTRSTAZ82";
			$amazon_search_url .= "&ResponseGroup=Medium";
			$amazon_search_url .= "&AssociateTag=$ass_id";
			$amazon_search_url .= "&Operation=ItemSearch";
			$amazon_search_url .= "&ItemPage={$clean['item_page']}";
			$amazon_search_url .= "&SearchIndex={$clean['category']}";
			if ( $clean['category'] != 'Blended' )
			{
				$amazon_search_url .= "&Sort=salesrank";
			}
			$amazon_search_url .= "&Keywords=" . urlencode( $clean['keyword'] );
			$amazon_search_url .= "&Style={$_AMZ_PREFS['xsl']}";
			$amazon_search_url .= "&Version=2007-10-29";
			
			// ...then ours
			$amazon_search_url .= "&ServerName=$amazon_server";
			$amazon_search_url .= "&a_server={$clean['a_server']}";
			$amazon_search_url .= "&Flag=$flag";
			$amazon_search_url .= "&ImgSize={$_AMZ_PREFS['image_size']}";
			$amazon_search_url .= "&keyword=" . urlencode( urlencode( $clean['keyword'] ) );		// [sic]
			
			// $display .= 'Search URL:    ' . $amazon_search_url . '<br/>';
			
			// grab search results from amazon
			$ch = curl_init( $amazon_search_url );
			
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			
			$results = curl_exec( $ch );
			
			if ( $err = curl_error( $ch ) )
			{
				$display .= $err . '<div class="error">Please report this to the site administrator.<div/>';
			}
			
			//$display .= 'Download speed =' . curl_getInfo( $ch, CURLINFO_SPEED_DOWNLOAD ) . '<br/>';
				 
			curl_close( $ch );
			
			// IF we are using ISO-8859-1
			//	THEN convert from UTF-8 so that the pound sign and German chars display properly
			if ( $LANG_CHARSET == 'iso-8859-1' )
			{
				$results = utf8_decode( $results );
			}
			
			$display .= $results;
			$display .= '</div>';
			
			return $display;
		}

		function _buildServerOptionList( $name, $selected )
		{
			$options = '<select name="' . $name . '" class="server-select">';
			$options .= '<option value="com" ' . ($selected == 'com' ? SELECTED : '') . '>com&nbsp;&nbsp;</option>';
			$options .= '<option value="ca" ' . ($selected == 'ca' ? SELECTED : '') . '>ca</option>';
			$options .= '<option value="uk" ' . ($selected == 'uk' ? SELECTED : '') . '>uk</option>';
			$options .= '<option value="fr" ' . ($selected == 'fr' ? SELECTED : '') . '>fr</option>';
			$options .= '<option value="de" ' . ($selected == 'de' ? SELECTED : '') . '>de</option>';
			$options .= '<option value="jp" ' . ($selected == 'jp' ? SELECTED : '') . '>jp</option>';
			$options .= '</select>';
			
			return $options;
		}
		
		// Make sure we have a valid server
		function _validServer( $a_server )
		{
			switch ( $a_server )
			{
				case 'com':
				case 'ca':
				case 'uk':
				case 'fr':
				case 'de':
				case 'jp':
					$clean_server = $a_server;
					break;
					
				case '':
					$clean_server = 'com';
					break;
					
				default:
					die( 'Invalid search data' );
			}
			
			return( $clean_server );
		}
		
		// Make sure we have a valid category
		function _validCategory( $category )
		{
			switch ( $category )
			{
				case 'Blended':
				case 'Books':
				case 'DVD':
				case 'Music':
				case 'Software':
					$clean_category = $category;
					break;
			
				case '':
					$clean_category = 'Blended';
					break;	
					
				default:
					die( 'Invalid search data' );
			}
			
			return( $clean_category );
		}
		
		// Clean up and return a keyword
		function _validKeyword( $keyword )
		{	
			$p = strip_tags( $keyword );
			$p = trim( $p );
			$p = attribute_escape( $p );
			
			return( $p );
		}
	} 
}


// Initialize the widget
function AMZ_Search_initWidgets()
{		
	function widget_amz_search($args)
	{
		global $gAMZ_Tools;
		
		extract($args);
		echo $before_widget;
		$gAMZ_Tools->getSearch()->printSearchBox();
		echo $after_widget;
	}

	register_sidebar_widget('Amazon Search', 'widget_amz_search');
}

function AMZ_Search_results()
{	
	global $gAMZ_Tools;
	
	$gAMZ_Tools->getSearch()->storeCleanRequestData();

	function AMZ_search()
	{
		global $gAMZ_Tools;
		
		return $gAMZ_Tools->getSearch()->getSearchResults();
	}

	include('wp-content/plugins/amazon-search/templates/search.php');
	exit;
}

?>
