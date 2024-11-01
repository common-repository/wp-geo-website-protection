<?php

class sg_Geo_IP2Country {
	

    private static $DATA_SECTION_SEPARATOR_SIZE = 16;
    private static $METADATA_START_MARKER = "\xAB\xCD\xEFMaxMind.com";
    private static $METADATA_START_MARKER_LENGTH = 14;
    private static $METADATA_MAX_SIZE = 131072; // 128 * 1024 = 128KB

    private $decoder;
    private $fileHandle;
    private $fileSize;
    private $ipV4Start;
    private $metadata;
    private $fileStream;
    private $pointerBase;
    // This is only used for unit testing
    private $pointerTestHack;
    private $switchByteOrder;

    private $types = [
        0 => 'extended',
        1 => 'pointer',
        2 => 'utf8_string',
        3 => 'double',
        4 => 'bytes',
        5 => 'uint16',
        6 => 'uint32',
        7 => 'map',
        8 => 'int32',
        9 => 'uint64',
        10 => 'uint128',
        11 => 'array',
        12 => 'container',
        13 => 'end_marker',
        14 => 'boolean',
        15 => 'float',
    ];
	
	private $map = array(
	'A1' => "Anonymous Proxy",
	'A2' => "Satellite Provider",
	'O1' => "Other Country",
	'AD' => "Andorra",
	'AE' => "United Arab Emirates",
	'AF' => "Afghanistan",
	'AG' => "Antigua and Barbuda",
	'AI' => "Anguilla",
	'AL' => "Albania",
	'AM' => "Armenia",
	'AO' => "Angola",
	'AP' => "Asia/Pacific Region",
	'AQ' => "Antarctica",
	'AR' => "Argentina",
	'AS' => "American Samoa",
	'AT' => "Austria",
	'AU' => "Australia",
	'AW' => "Aruba",
	'AX' => "Aland Islands",
	'AZ' => "Azerbaijan",
	'BA' => "Bosnia and Herzegovina",
	'BB' => "Barbados",
	'BD' => "Bangladesh",
	'BE' => "Belgium",
	'BF' => "Burkina Faso",
	'BG' => "Bulgaria",
	'BH' => "Bahrain",
	'BI' => "Burundi",
	'BJ' => "Benin",
	'BL' => "Saint Bartelemey",
	'BM' => "Bermuda",
	'BN' => "Brunei Darussalam",
	'BO' => "Bolivia",
	'BQ' => "Bonaire, Saint Eustatius and Saba",
	'BR' => "Brazil",
	'BS' => "Bahamas",
	'BT' => "Bhutan",
	'BV' => "Bouvet Island",
	'BW' => "Botswana",
	'BY' => "Belarus",
	'BZ' => "Belize",
	'CA' => "Canada",
	'CC' => "Cocos (Keeling) Islands",
	'CD' => "Congo, The Democratic Republic of the",
	'CF' => "Central African Republic",
	'CG' => "Congo",
	'CH' => "Switzerland",
	'CI' => "Cote d'Ivoire",
	'CK' => "Cook Islands",
	'CL' => "Chile",
	'CM' => "Cameroon",
	'CN' => "China",
	'CO' => "Colombia",
	'CR' => "Costa Rica",
	'CU' => "Cuba",
	'CV' => "Cape Verde",
	'CW' => "Curacao",
	'CX' => "Christmas Island",
	'CY' => "Cyprus",
	'CZ' => "Czech Republic",
	'DE' => "Germany",
	'DJ' => "Djibouti",
	'DK' => "Denmark",
	'DM' => "Dominica",
	'DO' => "Dominican Republic",
	'DZ' => "Algeria",
	'EC' => "Ecuador",
	'EE' => "Estonia",
	'EG' => "Egypt",
	'EH' => "Western Sahara",
	'ER' => "Eritrea",
	'ES' => "Spain",
	'ET' => "Ethiopia",
	'EU' => "Europe",
	'FI' => "Finland",
	'FJ' => "Fiji",
	'FK' => "Falkland Islands (Malvinas)",
	'FM' => "Micronesia, Federated States of",
	'FO' => "Faroe Islands",
	'FR' => "France",
	'GA' => "Gabon",
	'UK' => "United Kingdom",
	'GD' => "Grenada",
	'GE' => "Georgia",
	'GF' => "French Guiana",
	'GG' => "Guernsey",
	'GH' => "Ghana",
	'GI' => "Gibraltar",
	'GL' => "Greenland",
	'GM' => "Gambia",
	'GN' => "Guinea",
	'GP' => "Guadeloupe",
	'GQ' => "Equatorial Guinea",
	'GR' => "Greece",
	'GS' => "South Georgia and the South Sandwich Islands",
	'GT' => "Guatemala",
	'GU' => "Guam",
	'GW' => "Guinea-Bissau",
	'GY' => "Guyana",
	'HK' => "Hong Kong",
	'HM' => "Heard Island and McDonald Islands",
	'HN' => "Honduras",
	'HR' => "Croatia",
	'HT' => "Haiti",
	'HU' => "Hungary",
	'ID' => "Indonesia",
	'IE' => "Ireland",
	'IL' => "Israel",
	'IM' => "Isle of Man",
	'IN' => "India",
	'IO' => "British Indian Ocean Territory",
	'IQ' => "Iraq",
	'IR' => "Iran, Islamic Republic of",
	'IS' => "Iceland",
	'IT' => "Italy",
	'JE' => "Jersey",
	'JM' => "Jamaica",
	'JO' => "Jordan",
	'JP' => "Japan",
	'KE' => "Kenya",
	'KG' => "Kyrgyzstan",
	'KH' => "Cambodia",
	'KI' => "Kiribati",
	'KM' => "Comoros",
	'KN' => "Saint Kitts and Nevis",
	'KP' => "Korea, Democratic People's Republic of",
	'KR' => "Korea, Republic of",
	'KW' => "Kuwait",
	'KY' => "Cayman Islands",
	'KZ' => "Kazakhstan",
	'LA' => "Lao People's Democratic Republic",
	'LB' => "Lebanon",
	'LC' => "Saint Lucia",
	'LI' => "Liechtenstein",
	'LK' => "Sri Lanka",
	'LR' => "Liberia",
	'LS' => "Lesotho",
	'LT' => "Lithuania",
	'LU' => "Luxembourg",
	'LV' => "Latvia",
	'LY' => "Libyan Arab Jamahiriya",
	'MA' => "Morocco",
	'MC' => "Monaco",
	'MD' => "Moldova, Republic of",
	'ME' => "Montenegro",
	'MF' => "Saint Martin",
	'MG' => "Madagascar",
	'MH' => "Marshall Islands",
	'MK' => "Macedonia",
	'ML' => "Mali",
	'MM' => "Myanmar",
	'MN' => "Mongolia",
	'MO' => "Macao",
	'MP' => "Northern Mariana Islands",
	'MQ' => "Martinique",
	'MR' => "Mauritania",
	'MS' => "Montserrat",
	'MT' => "Malta",
	'MU' => "Mauritius",
	'MV' => "Maldives",
	'MW' => "Malawi",
	'MX' => "Mexico",
	'MY' => "Malaysia",
	'MZ' => "Mozambique",
	'NA' => "Namibia",
	'NC' => "New Caledonia",
	'NE' => "Niger",
	'NF' => "Norfolk Island",
	'NG' => "Nigeria",
	'NI' => "Nicaragua",
	'NL' => "Netherlands",
	'NO' => "Norway",
	'NP' => "Nepal",
	'NR' => "Nauru",
	'NU' => "Niue",
	'NZ' => "New Zealand",
	'OM' => "Oman",
	'PA' => "Panama",
	'PE' => "Peru",
	'PF' => "French Polynesia",
	'PG' => "Papua New Guinea",
	'PH' => "Philippines",
	'PK' => "Pakistan",
	'PL' => "Poland",
	'PM' => "Saint Pierre and Miquelon",
	'PN' => "Pitcairn",
	'PR' => "Puerto Rico",
	'PS' => "Palestinian Territory",
	'PT' => "Portugal",
	'PW' => "Palau",
	'PY' => "Paraguay",
	'QA' => "Qatar",
	'RE' => "Reunion",
	'RO' => "Romania",
	'RS' => "Serbia",
	'RU' => "Russian Federation",
	'RW' => "Rwanda",
	'SA' => "Saudi Arabia",
	'SB' => "Solomon Islands",
	'SC' => "Seychelles",
	'SD' => "Sudan",
	'SE' => "Sweden",
	'SG' => "Singapore",
	'SH' => "Saint Helena",
	'SI' => "Slovenia",
	'SJ' => "Svalbard and Jan Mayen",
	'SK' => "Slovakia",
	'SL' => "Sierra Leone",
	'SM' => "San Marino",
	'SN' => "Senegal",
	'SO' => "Somalia",
	'SR' => "Suriname",
	'ST' => "Sao Tome and Principe",
	'SV' => "El Salvador",
	'SX' => "Sint Maarten",
	'SY' => "Syrian Arab Republic",
	'SZ' => "Swaziland",
	'TC' => "Turks and Caicos Islands",
	'TD' => "Chad",
	'TF' => "French Southern Territories",
	'TG' => "Togo",
	'TH' => "Thailand",
	'TJ' => "Tajikistan",
	'TK' => "Tokelau",
	'TL' => "Timor-Leste",
	'TM' => "Turkmenistan",
	'TN' => "Tunisia",
	'TO' => "Tonga",
	'TR' => "Turkey",
	'TT' => "Trinidad and Tobago",
	'TV' => "Tuvalu",
	'TW' => "Taiwan",
	'TZ' => "Tanzania, United Republic of",
	'UA' => "Ukraine",
	'UG' => "Uganda",
	'UM' => "United States Minor Outlying Islands",
	'US' => "United States",
	'UY' => "Uruguay",
	'UZ' => "Uzbekistan",
	'VA' => "Holy See (Vatican City State)",
	'VC' => "Saint Vincent and the Grenadines",
	'VE' => "Venezuela",
	'VG' => "Virgin Islands, British",
	'VI' => "Virgin Islands, U.S.",
	'VN' => "Vietnam",
	'VU' => "Vanuatu",
	'WF' => "Wallis and Futuna",
	'WS' => "Samoa",
	'YE' => "Yemen",
	'YT' => "Mayotte",
	'ZA' => "South Africa",
	'ZM' => "Zambia",
	'ZW' => "Zimbabwe",
	"PS" => "Palestinian Territories",
	"AX" => "Aland Islands",
	"IM" => "Isle of Man",
	"TL" => "Timor-Leste"
	);	
	
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
        "NE" => "Niger",   // Niger
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
		"PS" - "Palestinian Territories",
		"AX" - "Aland Islands",
		"IM" - "Isle of Man",
		"TL" - "Timor-Leste"
    );
    
    public static $country_type_list = array(
        "AF" => "all 3rdcountry",   // Afghanistan
        "AL" => "all",   // Albania
        "DZ" => "all",   // Algeria
        "AS" => "all",   // American Samoa
        "AD" => "all",   // Andorra 
        "AO" => "all",   // Angola
        "AI" => "all",   // Anguilla
        "AQ" => "all",   // Antarctica
        "AG" => "all",   // Antigua and Barbuda
        "AR" => "all",   // Argentina
        "AM" => "all",   // Armenia
        "AW" => "all",   // Aruba 
        "AU" => "all",   // Australia 
        "AT" => "all europe",   // Austria
        "AZ" => "all",   // Azerbaijan
        "BS" => "all",   // Bahamas
        "BH" => "all",   // Bahrain 
        "BD" => "all",   // Bangladesh
        "BB" => "all",   // Barbados 
        "BY" => "all",   // Belarus 
        "BE" => "all europe",   // Belgium
        "BZ" => "all",   // Belize
        "BJ" => "all",   // Benin
        "BM" => "all",   // Bermuda
        "BT" => "all",   // Bhutan
        "BO" => "all",   // Bolivia
        "BA" => "all",   // Bosnia and Herzegovina
        "BW" => "all",   // Botswana
        "BV" => "all",   // Bouvet Island
        "BR" => "all",   // Brazil
        "IO" => "all",   // British Indian Ocean Territory
        "VG" => "all",   // British Virgin Islands,
        "BN" => "all",   // Brunei Darussalam
        "BG" => "all europe",   // Bulgaria
        "BF" => "all",   // Burkina Faso
        "BI" => "all 3rdcountry",   // Burundi
        "KH" => "all",   // Cambodia 
        "CM" => "all",   // Cameroon
        "CA" => "all",   // Canada 
        "CV" => "all",   // Cape Verde
        "KY" => "all",   // Cayman Islands
        "CF" => "all",   // Central African Republic
        "TD" => "all",   // Chad
        "CL" => "all",   // Chile
        "CN" => "all",   // China
        "CX" => "all",   // Christmas Island
        "CC" => "all",   // Cocos (Keeling Islands)
        "CO" => "all",   // Colombia
        "KM" => "all",   // Comoros
        "CG" => "all 3rdcountry",   // Congo 
        "CK" => "all",   // Cook Islands
        "CR" => "all",   // Costa Rica 
        "HR" => "all europe",   // Croatia (Hrvatska
        "CY" => "all europe",   // Cyprus
        "CZ" => "all europe",   // Czech Republic
        "CG" => "all",   // Democratic Republic of Congo,
        "DK" => "all europe",   // Denmark
        "DJ" => "all",   // Djibouti
        "DM" => "all",   // Dominica
        "DO" => "all",   // Dominican Republic
        "TP" => "all",   // East Timor
        "EC" => "all",   // Ecuador
        "EG" => "all",   // Egypt 
        "SV" => "all",   // El Salvador 
        "GQ" => "all",   // Equatorial Guinea
        "ER" => "all 3rdcountry",   // Eritrea 
        "EE" => "all europe",   // Estonia 
        "ET" => "all 3rdcountry",   // Ethiopia
        "FK" => "all",   // Falkland Islands (Malvinas)
        "FO" => "all",   // Faroe Islands 
        "FM" => "all",   // Federated States of Micronesia,
        "FJ" => "all",   // Fiji
        "FI" => "all europe",   // Finland
        "FR" => "all europe",   // France
        "GF" => "all",   // French Guiana
        "PF" => "all",   // French Polynesia
        "TF" => "all",   // French Southern Territories
        "GA" => "all",   // Gabon
        "GM" => "all",   // Gambia
        "GE" => "all",   // Georgia
        "DE" => "all europe",   // Germany
        "GH" => "all",   // Ghana
        "GI" => "all",   // Gibraltar
        "GR" => "all europe",   // Greece
        "GL" => "all",   // Greenland
        "GD" => "all",   // Grenada 
        "GP" => "all",   // Guadeloupe
        "GU" => "all",   // Guam 
        "GT" => "all",   // Guatemala
        "GN" => "all",   // Guinea
        "GW" => "all 3rdcountry",   // Guinea-Bissau
        "GY" => "all",   // Guyana
        "HT" => "all",   // Haiti
        "HM" => "all",   // Heard and McDonald Islands
        "HN" => "all",   // Honduras
        "HK" => "all",   // Hong Kong
        "HU" => "all europe",   // Hungary
        "IS" => "all",   // Iceland
        "IN" => "all",   // India
        "ID" => "all",   // Indonesia
        "IR" => "all",   // Iran
        "IQ" => "all",   // Iraq
        "IE" => "all europe",   // Ireland
        "IL" => "all",   // Israel
        "IT" => "all europe",   // Italy
        "CI" => "all",   // Ivory Coast,
        "JM" => "all",   // Jamaica
        "JP" => "all",   // Japan 
        "JO" => "all",   // Jordan 
        "KZ" => "all",   // Kazakhstan
        "KE" => "all",   // Kenya 
        "KI" => "all",   // Kiribati 
        "KW" => "all",   // Kuwait
        "KG" => "all",   // Kyrgyzstan
        "LA" => "all",   // Laos
        "LV" => "all europe",   // Latvia
        "LB" => "all",   // Lebanon
        "LS" => "all",   // Lesotho
        "LR" => "all 3rdcountry",   // Liberia 
        "LY" => "all",   // Libya
        "LI" => "all",   // Liechtenstein
        "LT" => "all europe",   // Lithuania
        "LU" => "all europe",   // Luxembourg 
        "MO" => "all",   // Macau
        "MK" => "all",   // Macedonia
        "MG" => "all 3rdcountry",   // Madagascar
        "MW" => "all 3rdcountry",   // Malawi
        "MY" => "all",   // Malaysia
        "MV" => "all",   // Maldives
        "ML" => "all",   // Mali
        "MT" => "all europe",   // Malta
        "MH" => "all",   // Marshall Islands
        "MQ" => "all",   // Martinique
        "MR" => "all",   // Mauritania
        "MU" => "all",   // Mauritius
        "YT" => "all",   // Mayotte
        "MX" => "all",   // Mexico
        "MD" => "all",   // Moldova
        "MC" => "all",   // Monaco
        "MN" => "all",   // Mongolia
        "MS" => "all",   // Montserrat
        "MA" => "all",   // Morocco
        "MZ" => "all",   // Mozambique
        "MM" => "all",   // Myanmar
        "NA" => "all",   // Namibia
        "NR" => "all",   // Nauru
        "NP" => "all",   // Nepal
        "NL" => "all europe",   // Netherlands
        "AN" => "all",   // Netherlands Antilles
        "NC" => "all",   // New Caledonia
        "NZ" => "all",   // New Zealand
        "NI" => "all",   // Nicaragua
        "NE" => "all 3rdcountry",   // Niger
        "NG" => "all",   // Nigeria
        "NU" => "all",   // Niue
        "NF" => "all",   // Norfolk Island
        "KP" => "all",   // Korea (North)
        "MP" => "all",   // Northern Mariana Islands
        "NO" => "all",   // Norway
        "OM" => "all",   // Oman
        "PK" => "all",   // Pakistan
        "PW" => "all",   // Palau
        "PA" => "all",   // Panama
        "PG" => "all",   // Papua New Guinea
        "PY" => "all",   // Paraguay
        "PE" => "all",   // Peru
        "PH" => "all",   // Philippines
        "PN" => "all",   // Pitcairn
        "PL" => "all europe",   // Poland
        "PT" => "all europe",   // Portugal
        "PR" => "all",   // Puerto Rico
        "QA" => "all",   // Qatar
        "RE" => "all",   // Reunion
        "RO" => "all europe",   // Romania
        "RU" => "all",   // Russian Federation
        "RW" => "all",   // Rwanda
        "SH" => "all",   // Saint Helena and Dependencies,
        "KN" => "all",   // Saint Kitts and Nevis
        "LC" => "all",   // Saint Lucia
        "VC" => "all",   // Saint Vincent and The Grenadines
        "VC" => "all",   // Saint Vincent and the Grenadines,
        "WS" => "all",   // Samoa
        "SM" => "all",   // San Marino
        "ST" => "all",   // Sao Tome and Principe 
        "SA" => "all",   // Saudi Arabia
        "SN" => "all",   // Senegal
        "SC" => "all",   // Seychelles
        "SL" => "all 3rdcountry",   // Sierra Leone
        "SG" => "all",   // Singapore
        "SK" => "all europe",   // Slovak Republic
        "SI" => "all europe",   // Slovenia
        "SB" => "all",   // Solomon Islands
        "SO" => "all",   // Somalia
        "ZA" => "all",   // South Africa
        "GS" => "all",   // S. Georgia and S. Sandwich Isls.
        "KR" => "all",   // South Korea,
        "ES" => "all europe",   // Spain
        "LK" => "all",   // Sri Lanka
        "SR" => "all",   // Suriname
        "SJ" => "all",   // Svalbard and Jan Mayen Islands
        "SZ" => "all",   // Swaziland
        "SE" => "all europe",   // Sweden
        "CH" => "all",   // Switzerland
        "SY" => "all",   // Syria
        "TW" => "all",   // Taiwan
        "TJ" => "all",   // Tajikistan
        "TZ" => "all 3rdcountry",   // Tanzania
        "TH" => "all",   // Thailand
        "TG" => "all",   // Togo
        "TK" => "all",   // Tokelau
        "TO" => "all",   // Tonga
        "TT" => "all",   // Trinidad and Tobago
        "TN" => "all",   // Tunisia
        "TR" => "all",   // Turkey
        "TM" => "all",   // Turkmenistan
        "TC" => "all",   // Turks and Caicos Islands
        "TV" => "all",   // Tuvalu
        "UG" => "all",   // Uganda
        "UA" => "all",   // Ukraine
        "AE" => "all",   // United Arab Emirates
        "UK" => "all europe",   // United Kingdom
        "US" => "all",   // United States
        "UM" => "all",   // US Minor Outlying Islands
        "UY" => "all",   // Uruguay
        "VI" => "all",   // US Virgin Islands,
        "UZ" => "all",   // Uzbekistan
        "VU" => "all",   // Vanuatu
        "VA" => "all",   // Vatican City State (Holy See)
        "VE" => "all",   // Venezuela
        "VN" => "all",   // Viet Nam
        "WF" => "all",   // Wallis and Futuna Islands
        "EH" => "all",   // Western Sahara
        "YE" => "all 3rdcountry",   // Yemen
        "ZM" => "all 3rdcountry",   // Zambia
        "ZW" => "all",   // Zimbabwe
        "CU" => "all",   // Cuba,
        "IR" => "all",   // Iran,
    );
	
    public function __construct()
    {
		$database = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'geo.mmdb';
        if (!is_readable($database)) return false;
		
        $this->fileHandle = @fopen($database, 'rb');

        if ($this->fileHandle === false) return false;
		
        $this->fileSize = @filesize($database);
        if ($this->fileSize === false) return false;

        $start = $this->findMetadataStart($database);
        $this->decoderFunc($this->fileHandle, $start);
        list($metadataArray) = $this->decode($start);
        $this->metadata = $this->setMetaData($metadataArray);
        $this->decoder = $this->decoderFunc(
            $this->fileHandle,
            $this->metadata->searchTreeSize + self::$DATA_SECTION_SEPARATOR_SIZE
        );
    }

	public function getNameByCountryCode($code){
		if(isset($this->map[$code])){
			return $this->map[$code];
		} else {
			return '';
		}
	}	


    public function getCountryByIP($ipAddress)
    {
        $record = $this->get($ipAddress);
        if ($record === null) return false;
        if (!is_array($record)) return false;

        return ($record['country']['iso_code'] == 'GB') ? 'UK' : $record['country']['iso_code'];
    }

    public function getContinentByIP($ipAddress)
    {
        $record = $this->get($ipAddress);
        if ($record === null) return false;
        if (!is_array($record)) return false;

        return $record->continent->code;
    }
	
    public function get($ipAddress)
    {
	
        if (!is_resource($this->fileHandle)) return false;

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) return false;

        if ($this->metadata->ipVersion === 4 && strrpos($ipAddress, ':')) return false;
        $pointer = $this->findAddressInTree($ipAddress);
        if ($pointer === 0) {
            return null;
        }

        return $this->resolveDataPointer($pointer);
    }

    private function findAddressInTree($ipAddress)
    {
        $rawAddress = array_merge(unpack('C*', inet_pton($ipAddress)));

        $bitCount = count($rawAddress) * 8;

        $node = $this->startNode($bitCount);

        for ($i = 0; $i < $bitCount; $i++) {
            if ($node >= $this->metadata->nodeCount) {
                break;
            }
            $tempBit = 0xFF & $rawAddress[$i >> 3];
            $bit = 1 & ($tempBit >> 7 - ($i % 8));

            $node = $this->readNode($node, $bit);
        }
        if ($node === $this->metadata->nodeCount) {
            return 0;
        } elseif ($node > $this->metadata->nodeCount) {
            return $node;
        }
        return false;
    }

    private function startNode($length)
    {

        if ($this->metadata->ipVersion === 6 && $length === 32) {
            return $this->ipV4StartNode();
        }

        return 0;
    }

    private function ipV4StartNode()
    {

        if ($this->metadata->ipVersion === 4) {
            return 0;
        }

        if ($this->ipV4Start) {
            return $this->ipV4Start;
        }
        $node = 0;

        for ($i = 0; $i < 96 && $node < $this->metadata->nodeCount; $i++) {
            $node = $this->readNode($node, 0);
        }
        $this->ipV4Start = $node;

        return $node;
    }

    private function readNode($nodeNumber, $index)
    {
        $baseOffset = $nodeNumber * $this->metadata->nodeByteSize;

        switch ($this->metadata->recordSize) {
            case 24:
                $bytes = $this->read($this->fileHandle, $baseOffset + $index * 3, 3);
                list(, $node) = unpack('N', "\x00" . $bytes);

                return $node;
            case 28:
                $middleByte = $this->read($this->fileHandle, $baseOffset + 3, 1);
                list(, $middle) = unpack('C', $middleByte);
                if ($index === 0) {
                    $middle = (0xF0 & $middle) >> 4;
                } else {
                    $middle = 0x0F & $middle;
                }
                $bytes = $this->read($this->fileHandle, $baseOffset + $index * 4, 3);
                list(, $node) = unpack('N', chr($middle) . $bytes);

                return $node;
            case 32:
                $bytes = $this->read($this->fileHandle, $baseOffset + $index * 4, 4);
                list(, $node) = unpack('N', $bytes);

                return $node;
            default:
                return false;
        }
    }

    private function resolveDataPointer($pointer)
    {
        $resolved = $pointer - $this->metadata->nodeCount
            + $this->metadata->searchTreeSize;
        if ($resolved > $this->fileSize) return false;

        list($data) = $this->decode($resolved);

        return $data;
    }

    private function findMetadataStart($filename)
    {
        $handle = $this->fileHandle;
        $fstat = fstat($handle);
        $fileSize = $fstat['size'];
        $marker = self::$METADATA_START_MARKER;
        $markerLength = self::$METADATA_START_MARKER_LENGTH;
        $metadataMaxLengthExcludingMarker
            = min(self::$METADATA_MAX_SIZE, $fileSize) - $markerLength;

        for ($i = 0; $i <= $metadataMaxLengthExcludingMarker; $i++) {
            for ($j = 0; $j < $markerLength; $j++) {
                fseek($handle, $fileSize - $i - $j - 1);
                $matchBit = fgetc($handle);
                if ($matchBit !== $marker[$markerLength - $j - 1]) {
                    continue 2;
                }
            }

            return $fileSize - $i;
        }
        return false;
    }

    public function metadata()
    {
        if (func_num_args()) return false;
        if (!is_resource($this->fileHandle)) return false;

        return $this->metadata;
    }

    public function close()
    {
        if (!is_resource($this->fileHandle)) return false;
        fclose($this->fileHandle);
    }
	


    public function setMetaData($metadata)
    {
        $this->metadata = new stdclass();
        $this->metadata->binaryFormatMajorVersion = $metadata['binary_format_major_version'];
        $this->metadata->binaryFormatMinorVersion = $metadata['binary_format_minor_version'];
        $this->metadata->buildEpoch = $metadata['build_epoch'];
        $this->metadata->databaseType = $metadata['database_type'];
        $this->metadata->languages = $metadata['languages'];
        $this->metadata->description = $metadata['description'];
        $this->metadata->ipVersion = $metadata['ip_version'];
        $this->metadata->nodeCount = $metadata['node_count'];
        $this->metadata->recordSize = $metadata['record_size'];
        $this->metadata->nodeByteSize = $this->metadata->recordSize / 4;
        $this->metadata->searchTreeSize = $this->metadata->nodeCount * $this->metadata->nodeByteSize;
		return $this->metadata;
    }

    public function read($stream, $offset, $numberOfBytes)
    {
        if ($numberOfBytes === 0) {
            return '';
        }
        if (fseek($stream, $offset) === 0) {
            $value = fread($stream, $numberOfBytes);

            if (ftell($stream) - $offset === $numberOfBytes) {
                return $value;
            }
        }
        return false;
    }

    public function decoderFunc(
        $fileStream,
        $pointerBase = 0,
        $pointerTestHack = false
    ) {
        $this->fileStream = $fileStream;
        $this->pointerBase = $pointerBase;
        $this->pointerTestHack = $pointerTestHack;

        $this->switchByteOrder = $this->isPlatformLittleEndian();
    }

    public function decode($offset)
    {
        list(, $ctrlByte) = unpack(
            'C',
            $this->read($this->fileStream, $offset, 1)
        );
        $offset++;

        $type = $this->types[$ctrlByte >> 5];

        if ($type === 'pointer') {
            list($pointer, $offset) = $this->decodePointer($ctrlByte, $offset);

            // for unit testing
            if ($this->pointerTestHack) {
                return [$pointer];
            }

            list($result) = $this->decode($pointer);

            return [$result, $offset];
        }

        if ($type === 'extended') {
            list(, $nextByte) = unpack(
                'C',
                $this->read($this->fileStream, $offset, 1)
            );

            $typeNum = $nextByte + 7;

            if ($typeNum < 8) return false;

            $type = $this->types[$typeNum];
            $offset++;
        }

        list($size, $offset) = $this->sizeFromCtrlByte($ctrlByte, $offset);

        return $this->decodeByType($type, $offset, $size);
    }

    private function decodeByType($type, $offset, $size)
    {
        switch ($type) {
            case 'map':
                return $this->decodeMap($size, $offset);
            case 'array':
                return $this->decodeArray($size, $offset);
            case 'boolean':
                return [$this->decodeBoolean($size), $offset];
        }

        $newOffset = $offset + $size;
        $bytes = $this->read($this->fileStream, $offset, $size);
        switch ($type) {
            case 'utf8_string':
                return [$this->decodeString($bytes), $newOffset];
            case 'double':
                $this->verifySize(8, $size);

                return [$this->decodeDouble($bytes), $newOffset];
            case 'float':
                $this->verifySize(4, $size);

                return [$this->decodeFloat($bytes), $newOffset];
            case 'bytes':
                return [$bytes, $newOffset];
            case 'uint16':
            case 'uint32':
                return [$this->decodeUint($bytes), $newOffset];
            case 'int32':
                return [$this->decodeInt32($bytes), $newOffset];
            case 'uint64':
            case 'uint128':
                return [$this->decodeBigUint($bytes, $size), $newOffset];
            default:
                return false;
        }
    }

    private function verifySize($expected, $actual)
    {
        if ($expected !== $actual) return false;
    }

    private function decodeArray($size, $offset)
    {
        $array = [];

        for ($i = 0; $i < $size; $i++) {
            list($value, $offset) = $this->decode($offset);
            array_push($array, $value);
        }

        return [$array, $offset];
    }

    private function decodeBoolean($size)
    {
        return $size === 0 ? false : true;
    }

    private function decodeDouble($bits)
    {
        // XXX - Assumes IEEE 754 double on platform
        list(, $double) = unpack('d', $this->maybeSwitchByteOrder($bits));

        return $double;
    }

    private function decodeFloat($bits)
    {
        // XXX - Assumes IEEE 754 floats on platform
        list(, $float) = unpack('f', $this->maybeSwitchByteOrder($bits));

        return $float;
    }

    private function decodeInt32($bytes)
    {
        $bytes = $this->zeroPadLeft($bytes, 4);
        list(, $int) = unpack('l', $this->maybeSwitchByteOrder($bytes));

        return $int;
    }

    private function decodeMap($size, $offset)
    {
        $map = [];

        for ($i = 0; $i < $size; $i++) {
            list($key, $offset) = $this->decode($offset);
            list($value, $offset) = $this->decode($offset);
            $map[$key] = $value;
        }

        return [$map, $offset];
    }

    private $pointerValueOffset = [
        1 => 0,
        2 => 2048,
        3 => 526336,
        4 => 0,
    ];

    private function decodePointer($ctrlByte, $offset)
    {
        $pointerSize = (($ctrlByte >> 3) & 0x3) + 1;

        $buffer = $this->read($this->fileStream, $offset, $pointerSize);
        $offset = $offset + $pointerSize;

        $packed = $pointerSize === 4
            ? $buffer
            : (pack('C', $ctrlByte & 0x7)) . $buffer;

        $unpacked = $this->decodeUint($packed);
        $pointer = $unpacked + $this->pointerBase
            + $this->pointerValueOffset[$pointerSize];

        return [$pointer, $offset];
    }

    private function decodeUint($bytes)
    {
        list(, $int) = unpack('N', $this->zeroPadLeft($bytes, 4));

        return $int;
    }

    private function decodeBigUint($bytes, $byteLength)
    {
        $maxUintBytes = log(PHP_INT_MAX, 2) / 8;

        if ($byteLength === 0) {
            return 0;
        }

        $numberOfLongs = ceil($byteLength / 4);
        $paddedLength = $numberOfLongs * 4;
        $paddedBytes = $this->zeroPadLeft($bytes, $paddedLength);
        $unpacked = array_merge(unpack("N$numberOfLongs", $paddedBytes));

        $integer = 0;

        $twoTo32 = '4294967296';

        foreach ($unpacked as $part) {
            if ($byteLength <= $maxUintBytes) {
                $integer = ($integer << 32) + $part;
            } elseif (extension_loaded('gmp')) {
                $integer = gmp_strval(gmp_add(gmp_mul($integer, $twoTo32), $part));
            } elseif (extension_loaded('bcmath')) {
                $integer = bcadd(bcmul($integer, $twoTo32), $part);
            } else return false;
        }

        return $integer;
    }

    private function decodeString($bytes)
    {
        return $bytes;
    }

    private function sizeFromCtrlByte($ctrlByte, $offset)
    {
        $size = $ctrlByte & 0x1f;
        $bytesToRead = $size < 29 ? 0 : $size - 28;
        $bytes = $this->read($this->fileStream, $offset, $bytesToRead);
        $decoded = $this->decodeUint($bytes);

        if ($size === 29) {
            $size = 29 + $decoded;
        } elseif ($size === 30) {
            $size = 285 + $decoded;
        } elseif ($size > 30) {
            $size = ($decoded & (0x0FFFFFFF >> (32 - (8 * $bytesToRead))))
                + 65821;
        }

        return [$size, $offset + $bytesToRead];
    }

    private function zeroPadLeft($content, $desiredLength)
    {
        return str_pad($content, $desiredLength, "\x00", STR_PAD_LEFT);
    }

    private function maybeSwitchByteOrder($bytes)
    {
        return $this->switchByteOrder ? strrev($bytes) : $bytes;
    }

    private function isPlatformLittleEndian()
    {
        $testint = 0x00FF;
        $packed = pack('S', $testint);

        return $testint === current(unpack('v', $packed));
    }

 

}