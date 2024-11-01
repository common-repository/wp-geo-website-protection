<?php
/**
 * SiteGuarding GEO checker (22 April 2020)
 */

error_reporting(0);
 
$debug_file = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'geodebug.txt';
if (is_file($debug_file)) {
	setcookie("geo_check", time(), time() + 3600 * 3);
	return;
}

$file_settings = dirname(__FILE__).'/settings.php';
if (file_exists($file_settings)) include_once($file_settings);
else {
	setcookie("geo_check", time(), time() + 3600 * 3);
	return;
}

$file_geo_class = dirname(__FILE__).'/geo.php';
if (file_exists($file_geo_class)) include_once($file_geo_class);
else {
	setcookie("geo_check", time(), time() + 3600 * 3);
	return;
}

$seo_sg_settings = (array)json_decode($seo_sg_settings, true);

if (isset($_COOKIE['geo_check'])) {
	if ($_COOKIE['geo_check'] == $seo_sg_settings['params_id']) return;
	unset($_COOKIE['geo_check']);
	setcookie("geo_check", "", time() - 3600);
}




$redirects = (isset($seo_sg_settings['redirects']) && $seo_sg_settings['redirects'] != '') ? (array)json_decode($seo_sg_settings['redirects'], true) : '';

$myIP = SEO_SG_Protection_2::GetMyIP();
$myCountryCode = SEO_SG_Protection_2::GetCountryCode($myIP);

if (substr($_SERVER["SCRIPT_FILENAME"], -12) != "wp-login.php" && strpos($_SERVER["SCRIPT_FILENAME"], "wp-admin") === false) {
	if (isset($seo_sg_settings['protection_frontend']) && $seo_sg_settings['protection_frontend'] == 1)
	{

		if (!SEO_SG_Protection_2::Check_IP_in_list($myIP, $seo_sg_settings['frontend_ip_list_allow']))
		{

			if ( !SEO_SG_Protection_2::Check_if_User_IP_allowed($myIP, $seo_sg_settings['frontend_ip_list']) )
			{
				// Log action
				$alert_data = array(
					'time' => time(),
					'ip' => $myIP,
					'country_code' => $myCountryCode,
					'url' => $_SERVER['REQUEST_URI']
				);
				SEO_SG_Protection_2::Save_Block_alert($alert_data);
				SEO_SG_Protection_2::BlockPage($myIP, $myCountryCode, $seo_sg_settings);
			}
			
			if ( !SEO_SG_Protection_2::Check_if_User_allowed($myCountryCode, json_decode($seo_sg_settings['frontend_country_list'], true)) )
			{
				// Log action
				$alert_data = array(
					'time' => time(),
					'ip' => $myIP,
					'country_code' => $myCountryCode,
					'url' => $_SERVER['REQUEST_URI']
				);
				SEO_SG_Protection_2::Save_Block_alert($alert_data);
				SEO_SG_Protection_2::BlockPage($myIP, $myCountryCode, $seo_sg_settings);
			}
		}
	}
} elseif (isset($seo_sg_settings['protection_backend']) && $seo_sg_settings['protection_backend'] == 1) {
	{
		
		if (!SEO_SG_Protection_2::Check_IP_in_list($myIP, $seo_sg_settings['backend_ip_list_allow']))
		{
			
			if ( !SEO_SG_Protection_2::Check_if_User_IP_allowed($myIP, $seo_sg_settings['backend_ip_list']) )
			{
				// Log action
				$alert_data = array(
					'time' => time(),
					'ip' => $myIP,
					'country_code' => $myCountryCode,
					'url' => $_SERVER['REQUEST_URI']
				);
				SEO_SG_Protection_2::Save_Block_alert($alert_data);
				SEO_SG_Protection_2::BlockPage($myIP, $myCountryCode, $seo_sg_settings);
			}
			
			if ( !SEO_SG_Protection_2::Check_if_User_allowed($myCountryCode, json_decode($seo_sg_settings['backend_country_list'], true)) )
			{
				// Log action
				$alert_data = array(
					'time' => time(),
					'ip' => $myIP,
					'country_code' => $myCountryCode,
					'url' => $_SERVER['REQUEST_URI']
				);
				SEO_SG_Protection_2::Save_Block_alert($alert_data);
				SEO_SG_Protection_2::BlockPage($myIP, $myCountryCode, $seo_sg_settings);
			}
		}
	}
}

if (is_array($redirects) && !empty($redirects)) {
	if (isset($redirects[$myCountryCode])) {
		if ($_SERVER['REQUEST_URI'] == $redirects[$myCountryCode]) return;
		if (substr($redirects[$myCountryCode], -6) == '%PATH%') {
			header("Location: " . str_replace("%PATH%", $_SERVER['REQUEST_URI'], $redirects[$myCountryCode]));
		} else {
			header("Location: " . $redirects[$myCountryCode]);
		}
		exit;
	}
}
	
	
if (!isset($_COOKIE['geo_check'])) setcookie("geo_check", $seo_sg_settings['params_id'], time() + 3600 * 3);

class SEO_SG_Protection_2
{
    public static $country_list = array(
        "AF" => "Afghanistan",   // Afghanistan
        "AL" => "Albania",   // Albania
        "DZ" => "Algeria",   // Algeria
        "AS" => "American Samoa",   // American Samoa
        "AD" => "Andorra",   // Andorra 
        "AO" => "Angola",   // Angola
        "AI" => "Anguilla",   // Anguilla
        "AQ" => "Antarctica",   // Antarctica
        "AG" => "Antigua and Barbuda",   // Antigua and Barbuda
        "AR" => "Argentina",   // Argentina
        "AM" => "Armenia",   // Armenia
        "AW" => "Aruba",   // Aruba 
        "AU" => "Australia",   // Australia 
        "AT" => "Austria",   // Austria
        "AZ" => "Azerbaijan",   // Azerbaijan
        "BS" => "Bahamas",   // Bahamas
        "BH" => "Bahrain",   // Bahrain 
        "BD" => "Bangladesh",   // Bangladesh
        "BB" => "Barbados",   // Barbados 
        "BY" => "Belarus",   // Belarus 
        "BE" => "Belgium",   // Belgium
        "BZ" => "Belize",   // Belize
        "BJ" => "Benin",   // Benin
        "BM" => "Bermuda",   // Bermuda
        "BT" => "Bhutan",   // Bhutan
        "BO" => "Bolivia",   // Bolivia
        "BA" => "Bosnia and Herzegovina",   // Bosnia and Herzegovina
        "BW" => "Botswana",   // Botswana
        "BV" => "Bouvet Island",   // Bouvet Island
        "BR" => "Brazil",   // Brazil
        "IO" => "British Indian Ocean Territory",   // British Indian Ocean Territory
        "VG" => "British Virgin Islands",   // British Virgin Islands,
        "BN" => "Brunei Darussalam",   // Brunei Darussalam
        "BG" => "Bulgaria",   // Bulgaria
        "BF" => "Burkina Faso",   // Burkina Faso
        "BI" => "Burundi",   // Burundi
        "KH" => "Cambodia",   // Cambodia 
        "CM" => "Cameroon",   // Cameroon
        "CA" => "Canada",   // Canada 
        "CV" => "Cape Verde",   // Cape Verde
        "KY" => "Cayman Islands",   // Cayman Islands
        "CF" => "Central African Republic",   // Central African Republic
        "TD" => "Chad",   // Chad
        "CL" => "Chile",   // Chile
        "CN" => "China",   // China
        "CX" => "Christmas Island",   // Christmas Island
        "CC" => "Cocos (Keeling Islands)",   // Cocos (Keeling Islands)
        "CO" => "Colombia",   // Colombia
        "KM" => "Comoros",   // Comoros
        "CG" => "Congo",   // Congo 
        "CK" => "Cook Islands",   // Cook Islands
        "CR" => "Costa Rica",   // Costa Rica 
        "HR" => "Croatia (Hrvatska)",   // Croatia (Hrvatska
        "CY" => "Cyprus",   // Cyprus
        "CZ" => "Czech Republic",   // Czech Republic
        "CG" => "Democratic Republic of Congo",   // Democratic Republic of Congo,
        "DK" => "Denmark",   // Denmark
        "DJ" => "Djibouti",   // Djibouti
        "DM" => "Dominica",   // Dominica
        "DO" => "Dominican Republic",   // Dominican Republic
        "TP" => "East Timor",   // East Timor
        "EC" => "Ecuador",   // Ecuador
        "EG" => "Egypt",   // Egypt 
        "SV" => "El Salvador",   // El Salvador 
        "GQ" => "Equatorial Guinea",   // Equatorial Guinea
        "ER" => "Eritrea",   // Eritrea 
        "EE" => "Estonia",   // Estonia 
        "ET" => "Ethiopia",   // Ethiopia
        "FK" => "Falkland Islands (Malvinas)",   // Falkland Islands (Malvinas)
        "FO" => "Faroe Islands",   // Faroe Islands 
        "FM" => "Federated States of Micronesia",   // Federated States of Micronesia,
        "FJ" => "Fiji",   // Fiji
        "FI" => "Finland",   // Finland
        "FR" => "France",   // France
        "GF" => "French Guiana",   // French Guiana
        "PF" => "French Polynesia",   // French Polynesia
        "TF" => "French Southern Territories",   // French Southern Territories
        "GA" => "Gabon",   // Gabon
        "GM" => "Gambia",   // Gambia
        "GE" => "Georgia",   // Georgia
        "DE" => "Germany",   // Germany
        "GH" => "Ghana",   // Ghana
        "GI" => "Gibraltar",   // Gibraltar
        "GR" => "Greece",   // Greece
        "GL" => "Greenland",   // Greenland
        "GD" => "Grenada",   // Grenada 
        "GP" => "Guadeloupe",   // Guadeloupe
        "GU" => "Guam",   // Guam 
        "GT" => "Guatemala",   // Guatemala
        "GN" => "Guinea",   // Guinea
        "GW" => "Guinea-Bissau",   // Guinea-Bissau
        "GY" => "Guyana",   // Guyana
        "HT" => "Haiti",   // Haiti
        "HM" => "Heard and McDonald Islands",   // Heard and McDonald Islands
        "HN" => "Honduras",   // Honduras
        "HK" => "Hong Kong",   // Hong Kong
        "HU" => "Hungary",   // Hungary
        "IS" => "Iceland",   // Iceland
        "IN" => "India",   // India
        "ID" => "Indonesia",   // Indonesia
        "IR" => "Iran",   // Iran
        "IQ" => "Iraq",   // Iraq
        "IE" => "Ireland",   // Ireland
        "IL" => "Israel",   // Israel
        "IT" => "Italy",   // Italy
        "CI" => "Ivory Coast",   // Ivory Coast,
        "JM" => "Jamaica",   // Jamaica
        "JP" => "Japan",   // Japan 
        "JO" => "Jordan",   // Jordan 
        "KZ" => "Kazakhstan",   // Kazakhstan
        "KE" => "Kenya",   // Kenya 
        "KI" => "Kiribati",   // Kiribati 
        "KW" => "Kuwait",   // Kuwait
        "KG" => "Kuwait",   // Kyrgyzstan
        "LA" => "Laos",   // Laos
        "LV" => "Latvia",   // Latvia
        "LB" => "Lebanon",   // Lebanon
        "LS" => "Lesotho",   // Lesotho
        "LR" => "Liberia",   // Liberia 
        "LY" => "Libya",   // Libya
        "LI" => "Liechtenstein",   // Liechtenstein
        "LT" => "Lithuania",   // Lithuania
        "LU" => "Luxembourg",   // Luxembourg 
        "MO" => "Macau",   // Macau
        "MK" => "Macedonia",   // Macedonia
        "MG" => "Madagascar",   // Madagascar
        "MW" => "Malawi",   // Malawi
        "MY" => "Malaysia",   // Malaysia
        "MV" => "Maldives",   // Maldives
        "ML" => "Mali",   // Mali
        "MT" => "Malta",   // Malta
        "MH" => "Marshall Islands",   // Marshall Islands
        "MQ" => "Martinique",   // Martinique
        "MR" => "Mauritania",   // Mauritania
        "MU" => "Mauritius",   // Mauritius
        "YT" => "Mayotte",   // Mayotte
        "MX" => "Mexico",   // Mexico
        "MD" => "Moldova",   // Moldova
        "MC" => "Monaco",   // Monaco
        "MN" => "Mongolia",   // Mongolia
        "MS" => "Montserrat",   // Montserrat
        "MA" => "Morocco",   // Morocco
        "MZ" => "Mozambique",   // Mozambique
        "MM" => "Myanmar",   // Myanmar
        "NA" => "Namibia",   // Namibia
        "NR" => "Nauru",   // Nauru
        "NP" => "Nepal",   // Nepal
        "NL" => "Netherlands",   // Netherlands
        "AN" => "Netherlands Antilles",   // Netherlands Antilles
        "NC" => "New Caledonia",   // New Caledonia
        "NZ" => "New Zealand",   // New Zealand
        "NI" => "Nicaragua",   // Nicaragua
        "NE" => "Nicaragua",   // Niger
        "NG" => "Nigeria",   // Nigeria
        "NU" => "Niue",   // Niue
        "NF" => "Norfolk Island",   // Norfolk Island
        "KP" => "Korea (North)",   // Korea (North)
        "MP" => "Northern Mariana Islands",   // Northern Mariana Islands
        "NO" => "Norway",   // Norway
        "OM" => "Oman",   // Oman
        "PK" => "Pakistan",   // Pakistan
        "PW" => "Palau",   // Palau
        "PA" => "Panama",   // Panama
        "PG" => "Papua New Guinea",   // Papua New Guinea
        "PY" => "Paraguay",   // Paraguay
        "PE" => "Peru",   // Peru
        "PH" => "Philippines",   // Philippines
        "PN" => "Pitcairn",   // Pitcairn
        "PL" => "Poland",   // Poland
        "PT" => "Portugal",   // Portugal
        "PR" => "Puerto Rico",   // Puerto Rico
        "QA" => "Qatar",   // Qatar
        "RE" => "Reunion",   // Reunion
        "RO" => "Romania",   // Romania
        "RU" => "Russian Federation",   // Russian Federation
        "RW" => "Rwanda",   // Rwanda
        "SH" => "Saint Helena and Dependencies",   // Saint Helena and Dependencies,
        "KN" => "Saint Kitts and Nevis",   // Saint Kitts and Nevis
        "LC" => "Saint Lucia",   // Saint Lucia
        "VC" => "Saint Vincent and The Grenadines",   // Saint Vincent and The Grenadines
        "VC" => "Saint Vincent and the Grenadines",   // Saint Vincent and the Grenadines,
        "WS" => "Samoa",   // Samoa
        "SM" => "San Marino",   // San Marino
        "ST" => "Sao Tome and Principe",   // Sao Tome and Principe 
        "SA" => "Saudi Arabia",   // Saudi Arabia
        "SN" => "Senegal",   // Senegal
        "SC" => "Seychelles",   // Seychelles
        "SL" => "Sierra Leone",   // Sierra Leone
        "SG" => "Singapore",   // Singapore
        "SK" => "Slovak Republic",   // Slovak Republic
        "SI" => "Slovenia",   // Slovenia
        "SB" => "Solomon Islands",   // Solomon Islands
        "SO" => "Somalia",   // Somalia
        "ZA" => "South Africa",   // South Africa
        "GS" => "S. Georgia and S. Sandwich Isls.",   // S. Georgia and S. Sandwich Isls.
        "KR" => "South Korea",   // South Korea,
        "ES" => "Spain",   // Spain
        "LK" => "Sri Lanka",   // Sri Lanka
        "SR" => "Suriname",   // Suriname
        "SJ" => "Svalbard and Jan Mayen Islands",   // Svalbard and Jan Mayen Islands
        "SZ" => "Swaziland",   // Swaziland
        "SE" => "Sweden",   // Sweden
        "CH" => "Switzerland",   // Switzerland
        "SY" => "Syria",   // Syria
        "TW" => "Taiwan",   // Taiwan
        "TJ" => "Tajikistan",   // Tajikistan
        "TZ" => "Tanzania",   // Tanzania
        "TH" => "Thailand",   // Thailand
        "TG" => "Togo",   // Togo
        "TK" => "Tokelau",   // Tokelau
        "TO" => "Tonga",   // Tonga
        "TT" => "Trinidad and Tobago",   // Trinidad and Tobago
        "TN" => "Tunisia",   // Tunisia
        "TR" => "Turkey",   // Turkey
        "TM" => "Turkmenistan",   // Turkmenistan
        "TC" => "Turks and Caicos Islands",   // Turks and Caicos Islands
        "TV" => "Tuvalu",   // Tuvalu
        "UG" => "Uganda",   // Uganda
        "UA" => "Ukraine",   // Ukraine
        "AE" => "United Arab Emirates",   // United Arab Emirates
        "UK" => "United Kingdom",   // United Kingdom
        "US" => "United States",   // United States
        "UM" => "US Minor Outlying Islands",   // US Minor Outlying Islands
        "UY" => "Uruguay",   // Uruguay
        "VI" => "US Virgin Islands",   // US Virgin Islands,
        "UZ" => "Uzbekistan",   // Uzbekistan
        "VU" => "Vanuatu",   // Vanuatu
        "VA" => "Vatican City State (Holy See)",   // Vatican City State (Holy See)
        "VE" => "Venezuela",   // Venezuela
        "VN" => "Viet Nam",   // Viet Nam
        "WF" => "Wallis and Futuna Islands",   // Wallis and Futuna Islands
        "EH" => "Western Sahara",   // Western Sahara
        "YE" => "Yemen",   // Yemen
        "ZM" => "Zambia",   // Zambia
        "ZW" => "Zimbabwe",   // Zimbabwe
        "CU" => "Cuba",   // Cuba,
        "IR" => "Iran",   // Iran,
    );
    
    public static function GetMyIP()
    {
		$ip_address = $_SERVER["REMOTE_ADDR"];
		if (isset($_SERVER["HTTP_X_REAL_IP"])) $ip_address = $_SERVER["HTTP_X_REAL_IP"];
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) $ip_address = $_SERVER["HTTP_CF_CONNECTING_IP"];
        return $ip_address ;
    }

    public static function Check_IP_in_list($ip, $ip_list = '')
    {
        if ($ip_list == '') return false;   // IP is not in the list
        
        $ip_list = str_replace(array(".*.*.*", ".*.*", ".*"), ".", trim($ip_list));
        $ip_list = explode("\n", $ip_list);
        if (count($ip_list))
        {
            foreach ($ip_list as $rule_ip)
            {
				if ($rule_ip['0'] == '#') continue;
				$rule_ip = trim($rule_ip);
                if (strpos($ip, $rule_ip) === 0) 
                {
                    // match
                    return true;    // IP is in the list
                }
            }
        }
        
        return  false;   // IP is not in the list
    }
    
    public static function Check_if_User_IP_allowed($ip, $ip_list = '')
    {
        if ($ip_list == '') return true;
        
        $ip_list = str_replace(array(".*.*.*", ".*.*", ".*"), ".", trim($ip_list));
        $ip_list = explode("\n", $ip_list);
        if (count($ip_list))
        {
            foreach ($ip_list as $rule_ip)
            {
				$rule_ip = trim($rule_ip);
                if (strpos($ip, $rule_ip) === 0) 
                {
                    // match
                    return false;
                }
            }
        }
        
        return true;
    }

    public static function GetCountryCode($ip)
    {
        if (isset($_COOKIE["GEO_country_code"]) && isset($_COOKIE["GEO_country_code_hash"]))
        {
            $cookie_GEO_country_code = trim($_COOKIE["GEO_country_code"]);
            $cookie_GEO_country_code_hash = trim($_COOKIE["GEO_country_code_hash"]);
            
            $hash = md5($ip.'-'.$cookie_GEO_country_code);
            if ($cookie_GEO_country_code_hash == $hash) return $cookie_GEO_country_code;
        }
        
        if (!class_exists('sg_Geo_IP2Country'))
        {
            include_once(dirname(__FILE__).DIRSEP.'geo.php');
        }
        
        $geo = new sg_Geo_IP2Country;
        $country_code = $geo->getCountryByIP($ip); 
        
        if ($country_code != '')
        {
            // Set cookie
            $hash = md5($ip.'-'.$country_code);
            setcookie("GEO_country_code", $country_code, time()+3600*24);
            setcookie("GEO_country_code_hash", $hash, time()+3600*24);
        }
        
        return $country_code;
    }
    
    public static function Check_if_User_allowed($myCountryCode, $blocked_country_list = array())
    {
        if (count($blocked_country_list) && in_array($myCountryCode, $blocked_country_list)) return false;
        return true;
    }
    
    
    public static function Save_Block_alert($alert_data)
    {
        $sql_array = array(
            'time' => intval($alert_data['time']),
            'ip' => $alert_data['ip'],
            'country_code' => $alert_data['country_code'],
            'url' => addslashes($alert_data['url']),
        );
        
        $file_tmp_block_log = dirname(__FILE__).'/block.log';
        $fp = fopen($file_tmp_block_log, 'a');
        fwrite($fp, json_encode($sql_array)."\n");
        fclose($fp);
    }
    
    
    public static function BlockPage($myIP, $myCountryCode = '', $data)
    {
        $blockpage_json = array();
        $blockpage_json['logo_url'] = '/wp-content/plugins/wp-geo-website-protection/images/logo_siteguarding.svg';
        $blockpage_json['text_1'] = 'Access is not allowed from your IP or your country.';
        $blockpage_json['text_2'] = 'If you think it\'s a mistake, please contactwith the webmaster of the website';
        $blockpage_json['hide_debug'] = 0;
        $blockpage_json['hide_ipinfo'] = 0;


            
		if (isset($data['custom_status']) && intval($data['custom_status']) == 1)
		{
			// Replace default settings with customized
			if ($data['logo_url'] != '') $blockpage_json['logo_url'] = str_replace("!APOS!", "'", $data['logo_url']);
			if ($data['text_1'] != '') $blockpage_json['text_1'] = str_replace("!APOS!", "'", $data['text_1']);
			if ($data['text_2'] != '') $blockpage_json['text_2'] = str_replace("!APOS!", "'", $data['text_2']);
			
			$blockpage_json['hide_debug'] = intval($data['hide_debug']);
			$blockpage_json['hide_ipinfo'] = intval($data['hide_ipinfo']);

		}

		$logo_url = '';
        if ($blockpage_json['logo_url'] != '') $logo_url = '<p><img style="max-width:500px;max-height:400px" src="'.$blockpage_json['logo_url'].'" id="logo"></p>';

        $debug_info = '';
        if ($blockpage_json['hide_debug'] == 0) $debug_info = '<p>If you the owner of the website. Please enable DEBUG mode in your WordPress (use FTP) to disable GEO Protection.<br>
            Read more about it on <a target="_blank" href="https://codex.wordpress.org/Debugging_in_WordPress">Debugging in WordPress</a> or contact with <a target="_blank" href="https://www.siteguarding.com/en/contacts">SiteGuarding.com support</a></p>';        

        $ipinfo = '';
        if ($blockpage_json['hide_ipinfo'] == 0) {
			$ipinfo = '<h4>Session details:</h4><p>IP: '.$myIP.'</p>';
			if ($myCountryCode != '') $ipinfo .= '<p>Country: '.SEO_SG_Protection_2::$country_list[$myCountryCode].'</p>';
		}
		
		
        ?><html><head>
        </head>
        <body>
        <div style="margin:100px auto; max-width: 500px;text-align: center;">
			<?php echo $logo_url; ?>
            <p>&nbsp;</p>
            <h3 style="color: #de0027; text-align: center;"><?php echo $blockpage_json['text_1']; ?></h3>
            <p><?php echo $blockpage_json['text_2']; ?></p>
            
            
            <?php echo $debug_info; ?>

            <?php echo $ipinfo; ?>
            <p>&nbsp;</p>
            <p>&nbsp;</p>

            <p style="font-size: 70%;">Powered by <a target="_blank" href="https://www.siteguarding.com/">SiteGuarding.com</a></p>


        </div>
        </body></html>
        <?php

        die();
    }

}

?>