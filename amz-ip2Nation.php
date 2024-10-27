<?php

if (!class_exists('AMZ_ip2nation'))
{
	// simple class to deal with our ip2Nation database
	class AMZ_ip2nation
	{
		function isDBinstalled()
		{
			global $wpdb;
			
			return $wpdb->get_var("SHOW TABLES LIKE 'ip2nation'") == 'ip2nation';
		}
		
		// Get the amazon server geolocated by IP
		function getAmazonServerFromIP( $default_server )
		{
			global $wpdb;
		
			if ( !$this->isDBinstalled() )
				return $default_server;
							
			$sql = 'SELECT country FROM ip2nation
						WHERE 
	            			ip < INET_ATON("'.$_SERVER['REMOTE_ADDR'].'") 
	        			ORDER BY 
	            			ip DESC 
	        			LIMIT 0,1';
	
			$row = $wpdb->get_row( $sql, ARRAY_A );
	
			// Choose the best amazon location based on geography and language...
			
			// This could be done more efficiently in a database, but for now this is easier until it's more complete.
			
			// I could use some help here: some of my categorization may be incorrect or incomplete - please let me
			//	know of any glaring problems.
			
			switch ($row['country'])
			{
				case 'us':	// United States
				case 'mx':	// Mexico
				case 'an':	// Netherlands Antilles
				case 'nz':	// New Zealand
				case 'kn':	// Saint Kitts and Nevis
				case 'lc':	// Saint Lucia
				case 'vg':	// Virgin Islands (British)
				case 'vi':	// Virgin Islands (U.S.)
					return 'com';

				case 'uk':	// United Kingdom
				case 'gb':	// Great Britain (UK)
				case 'gi':	// Gibraltar
				case 'ie':	// Ireland
					return 'uk';
					
				// Not necessarily English-speaking, but maybe order from UK?
				case 'eu':	// Europe
				case 'dk':	// Denmark
				case 'fi':	// Finland
				case 'gr':	// Greece
				case 'is':	// Iceland
				case 'nl':	// Netherlands
				case 'no':	// Norway
				case 'za':	// South Africa
				case 'se':	// Sweden
					return 'uk';

				case 'ca':	// Canada
				case 'pm':	// St. Pierre and Miquelon
					return 'ca';
					
				case 'de':	// Germany
				case 'at':	// Austria
				case 'li':	// Liechtenstein				
				case 'na':	// Namibia				
				case 'ch':	// Switzerland				
					return 'de';
						
				// Not necessarily German-speaking, but maybe order from Germany?
				case 'cz':	// Czech Republic
				case 'pl':	// Poland
				case 'sk':	// Slovak Republic
					return 'de';
				
				case 'fr':	// France
				case 'be':	// Belgium
				case 'bj':	// Benin
				case 'bf':	// Burkina Faso
				case 'bi':	// Burundi
				case 'cm':	// Cameroon
				case 'cf':	// Central African Republic
				case 'td':	// Chad
				case 'km':	// Comoros
				case 'cg':	// Congo
				case 'dj':	// Djibouti
				case 'ga':	// Gabon
				case 'gp':	// Guadeloupe
				case 'gf':	// French Guiana
				case 'pf':	// French Polynesia
				case 'tf':	// French Southern Territories
				case 'ht':	// Haiti
				case 'ci':	// Ivory Coast
				case 'lu':	// Luxembourg
				case 'mg':	// Madagascar
				case 'ml':	// Mali
				case 'mq':	// Martinique
				case 'yt':	// Mayotte
				case 'mc':	// Monaco
				case 'nc':	// New Caledonia
				case 'ne':	// Niger
				case 're':	// Reunion
				case 'sn':	// Senegal
				case 'sc':	// Seychelles
				case 'tg':	// Togo
				case 'vu':	// Vanuatu
				case 'wf':	// Wallis and Futuna Islands
					return 'fr';
					
				case 'jp':
					return 'jp';
										
				default:
					break;
			}
	
			return $default_server;
		}
	}
}

?>
