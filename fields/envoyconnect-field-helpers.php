<?php

function envoyconnect_helper_state() {
	$states = apply_filters( 'envoyconnect_helper_state', envoyconnect_get_helper_states() );
	$countries = envoyconnect_helper_country();
	foreach ( $states as $key => $val ) {
		if ( isset( $countries[$key] ) )
			$new_states[$countries[$key]] = $val;
	}
	return $new_states;

}

function envoyconnect_merge_helper_locs() {
	$states = apply_filters( 'envoyconnect_merge_helper_states', envoyconnect_get_helper_states() );
	$countries = envoyconnect_helper_country();
	foreach ( $countries as $key => $val ) {
		if ( isset( $states[$key] ) ) {
			foreach( $states[$key] as $skey => $sval ) {
				if ( is_array( $sval ) ) {
					foreach ( $sval as $svalkey => $svalval ) {
						$new_states[$key.'_'.$svalkey] = $svalval;
					}
				} else {
					$new_states[$key.'_'.$skey] = $sval;
				}
			}
			$new_locs[$key] = $new_states;
			unset($new_states);
		} else {
			$new_locs[$key] = $val;
		}
	}
	return $new_locs;
}

function envoyconnect_get_helper_states() {
	
	// OZ -- NOTE: WE'VE MODIFIED SOUTH AUSTRALIA, WESTERN AUSTRALIA AND NORTHERN TERRITORY TO PREVENT CONFLICTS AND STANDARDIZE THE ABBREVIATIONS
	return apply_filters( 'envoyconnect_get_helper_states', array(
		'AU' => array(
			'ACT' => __('Australian Capital Territory', 'envoyconnect'), 
			'NSW' => __('New South Wales', 'envoyconnect'), 
			'NTA' => __('Northern Territory', 'envoyconnect'), 
			'QLD' => __('Queensland', 'envoyconnect'), 
			'SAA' => __('South Australia', 'envoyconnect'), 
			'TAS' => __('Tasmania', 'envoyconnect'), 
			'VIC' => __('Victoria', 'envoyconnect'), 
			'WAA' => __('Western Australia', 'envoyconnect'),
		),
		'CA' => array(
			'AB' => __('Alberta', 'envoyconnect'), 
			'BC' => __('British Columbia', 'envoyconnect'), 
			'MB' => __('Manitoba', 'envoyconnect'), 
			'NB' => __('New Brunswick', 'envoyconnect'), 
			'NF' => __('Newfoundland', 'envoyconnect'), 
			'NT' => __('Northwest Territories', 'envoyconnect'), 
			'NS' => __('Nova Scotia', 'envoyconnect'), 
			'NU' => __('Nunavut', 'envoyconnect'), 
			'ON' => __('Ontario', 'envoyconnect'), 
			'PE' => __('Prince Edward Island', 'envoyconnect'), 
			'QC' => __('Quebec', 'envoyconnect'), 
			'SK' => __('Saskatchewan', 'envoyconnect'), 
			'YT' => __('Yukon Territory', 'envoyconnect'),
		),
		'US' => array(
			'AL' => __('Alabama', 'envoyconnect'), 
			'AK' => __('Alaska', 'envoyconnect'), 
			'AZ' => __('Arizona', 'envoyconnect'), 
			'AR' => __('Arkansas', 'envoyconnect'), 
			'CA' => __('California', 'envoyconnect'), 
			'CO' => __('Colorado', 'envoyconnect'), 
			'CT' => __('Connecticut', 'envoyconnect'), 
			'DE' => __('Delaware', 'envoyconnect'), 
			'DC' => __('District Of Columbia', 'envoyconnect'), 
			'FL' => __('Florida', 'envoyconnect'), 
			'GA' => __('Georgia', 'envoyconnect'), 
			'HI' => __('Hawaii', 'envoyconnect'), 
			'ID' => __('Idaho', 'envoyconnect'), 
			'IL' => __('Illinois', 'envoyconnect'), 
			'IN' => __('Indiana', 'envoyconnect'), 
			'IA' => __('Iowa', 'envoyconnect'), 
			'KS' => __('Kansas', 'envoyconnect'), 
			'KY' => __('Kentucky', 'envoyconnect'), 
			'LA' => __('Louisiana', 'envoyconnect'), 
			'ME' => __('Maine', 'envoyconnect'), 
			'MD' => __('Maryland', 'envoyconnect'), 
			'MA' => __('Massachusetts', 'envoyconnect'), 
			'MI' => __('Michigan', 'envoyconnect'), 
			'MN' => __('Minnesota', 'envoyconnect'), 
			'MS' => __('Mississippi', 'envoyconnect'), 
			'MO' => __('Missouri', 'envoyconnect'), 
			'MT' => __('Montana', 'envoyconnect'), 
			'NE' => __('Nebraska', 'envoyconnect'), 
			'NV' => __('Nevada', 'envoyconnect'), 
			'NH' => __('New Hampshire', 'envoyconnect'), 
			'NJ' => __('New Jersey', 'envoyconnect'), 
			'NM' => __('New Mexico', 'envoyconnect'), 
			'NY' => __('New York', 'envoyconnect'), 
			'NC' => __('North Carolina', 'envoyconnect'), 
			'ND' => __('North Dakota', 'envoyconnect'), 
			'OH' => __('Ohio', 'envoyconnect'), 
			'OK' => __('Oklahoma', 'envoyconnect'), 
			'OR' => __('Oregon', 'envoyconnect'), 
			'PA' => __('Pennsylvania', 'envoyconnect'), 
			'RI' => __('Rhode Island', 'envoyconnect'), 
			'SC' => __('South Carolina', 'envoyconnect'), 
			'SD' => __('South Dakota', 'envoyconnect'), 
			'TN' => __('Tennessee', 'envoyconnect'), 
			'TX' => __('Texas', 'envoyconnect'), 
			'UT' => __('Utah', 'envoyconnect'), 
			'VT' => __('Vermont', 'envoyconnect'), 
			'VA' => __('Virginia', 'envoyconnect'), 
			'WA' => __('Washington', 'envoyconnect'), 
			'WV' => __('West Virginia', 'envoyconnect'), 
			'WI' => __('Wisconsin', 'envoyconnect'), 
			'WY' => __('Wyoming', 'envoyconnect'),
			'AA' => __('Armed Forces Americas', 'envoyconnect'), 
			'AE' => __('Armed Forces Europe', 'envoyconnect'), 
			'AP' => __('Armed Forces Pacific', 'envoyconnect'),
			'AS' => __('American Samoa', 'envoyconnect'),
			'FM' => __('Micronesia', 'envoyconnect'),
			'GU' => __('Guam', 'envoyconnect'),
			'MH' => __('Marshall Islands', 'envoyconnect'),
			'PR' => __('Puerto Rico', 'envoyconnect'),
			'VI' => __('U.S. Virgin Islands', 'envoyconnect'),
		),
	) );
	
}

function envoyconnect_helper_country() {
	
	return apply_filters( 'envoyconnect_helper_country', array(
				'AF' => __('Afghanistan', 'envoyconnect'),
				'AX' => __('&#197;land Islands', 'envoyconnect'),
				'AL' => __('Albania', 'envoyconnect'),
				'DZ' => __('Algeria', 'envoyconnect'),
				'AD' => __('Andorra', 'envoyconnect'),
				'AO' => __('Angola', 'envoyconnect'),
				'AI' => __('Anguilla', 'envoyconnect'),
				'AQ' => __('Antarctica', 'envoyconnect'),
				'AG' => __('Antigua and Barbuda', 'envoyconnect'),
				'AR' => __('Argentina', 'envoyconnect'),
				'AM' => __('Armenia', 'envoyconnect'),
				'AW' => __('Aruba', 'envoyconnect'),
				'AU' => __('Australia', 'envoyconnect'),
				'AT' => __('Austria', 'envoyconnect'),
				'AZ' => __('Azerbaijan', 'envoyconnect'),
				'BS' => __('Bahamas', 'envoyconnect'),
				'BH' => __('Bahrain', 'envoyconnect'),
				'BD' => __('Bangladesh', 'envoyconnect'),
				'BB' => __('Barbados', 'envoyconnect'),
				'BY' => __('Belarus', 'envoyconnect'),
				'BE' => __('Belgium', 'envoyconnect'),
				'BZ' => __('Belize', 'envoyconnect'),
				'BJ' => __('Benin', 'envoyconnect'),
				'BM' => __('Bermuda', 'envoyconnect'),
				'BT' => __('Bhutan', 'envoyconnect'),
				'BO' => __('Bolivia', 'envoyconnect'),
				'BA' => __('Bosnia and Herzegovina', 'envoyconnect'),
				'BW' => __('Botswana', 'envoyconnect'),
				'BR' => __('Brazil', 'envoyconnect'),
				'IO' => __('British Indian Ocean Territory', 'envoyconnect'),
				'VG' => __('British Virgin Islands', 'envoyconnect'),
				'BN' => __('Brunei', 'envoyconnect'),
				'BG' => __('Bulgaria', 'envoyconnect'),
				'BF' => __('Burkina Faso', 'envoyconnect'),
				'BI' => __('Burundi', 'envoyconnect'),
				'KH' => __('Cambodia', 'envoyconnect'),
				'CM' => __('Cameroon', 'envoyconnect'),
				'CA' => __('Canada', 'envoyconnect'),
				'CV' => __('Cape Verde', 'envoyconnect'),
				'KY' => __('Cayman Islands', 'envoyconnect'),
				'CF' => __('Central African Republic', 'envoyconnect'),
				'TD' => __('Chad', 'envoyconnect'),
				'CL' => __('Chile', 'envoyconnect'),
				'CN' => __('China', 'envoyconnect'),
				'CX' => __('Christmas Island', 'envoyconnect'),
				'CC' => __('Cocos (Keeling) Islands', 'envoyconnect'),
				'CO' => __('Colombia', 'envoyconnect'),
				'KM' => __('Comoros', 'envoyconnect'),
				'CG' => __('Congo (Brazzaville)', 'envoyconnect'),
				'CD' => __('Congo (Kinshasa)', 'envoyconnect'),
				'CK' => __('Cook Islands', 'envoyconnect'),
				'CR' => __('Costa Rica', 'envoyconnect'),
				'HR' => __('Croatia', 'envoyconnect'),
				'CU' => __('Cuba', 'envoyconnect'),
				'CY' => __('Cyprus', 'envoyconnect'),
				'CZ' => __('Czech Republic', 'envoyconnect'),
				'DK' => __('Denmark', 'envoyconnect'),
				'DJ' => __('Djibouti', 'envoyconnect'),
				'DM' => __('Dominica', 'envoyconnect'),
				'DO' => __('Dominican Republic', 'envoyconnect'),
				'EC' => __('Ecuador', 'envoyconnect'),
				'EG' => __('Egypt', 'envoyconnect'),
				'SV' => __('El Salvador', 'envoyconnect'),
				'GQ' => __('Equatorial Guinea', 'envoyconnect'),
				'ER' => __('Eritrea', 'envoyconnect'),
				'EE' => __('Estonia', 'envoyconnect'),
				'ET' => __('Ethiopia', 'envoyconnect'),
				'FK' => __('Falkland Islands', 'envoyconnect'),
				'FO' => __('Faroe Islands', 'envoyconnect'),
				'FJ' => __('Fiji', 'envoyconnect'),
				'FI' => __('Finland', 'envoyconnect'),
				'FR' => __('France', 'envoyconnect'),
				'GF' => __('French Guiana', 'envoyconnect'),
				'PF' => __('French Polynesia', 'envoyconnect'),
				'TF' => __('French Southern Territories', 'envoyconnect'),
				'GA' => __('Gabon', 'envoyconnect'),
				'GM' => __('Gambia', 'envoyconnect'),
				'GE' => __('Georgia', 'envoyconnect'),
				'DE' => __('Germany', 'envoyconnect'),
				'GH' => __('Ghana', 'envoyconnect'),
				'GI' => __('Gibraltar', 'envoyconnect'),
				'GR' => __('Greece', 'envoyconnect'),
				'GL' => __('Greenland', 'envoyconnect'),
				'GD' => __('Grenada', 'envoyconnect'),
				'GP' => __('Guadeloupe', 'envoyconnect'),
				'GT' => __('Guatemala', 'envoyconnect'),
				'GG' => __('Guernsey', 'envoyconnect'),
				'GN' => __('Guinea', 'envoyconnect'),
				'GW' => __('Guinea-Bissau', 'envoyconnect'),
				'GY' => __('Guyana', 'envoyconnect'),
				'HT' => __('Haiti', 'envoyconnect'),
				'HN' => __('Honduras', 'envoyconnect'),
				'HK' => __('Hong Kong', 'envoyconnect'),
				'HU' => __('Hungary', 'envoyconnect'),
				'IS' => __('Iceland', 'envoyconnect'),
				'IN' => __('India', 'envoyconnect'),
				'ID' => __('Indonesia', 'envoyconnect'),
				'IR' => __('Iran', 'envoyconnect'),
				'IQ' => __('Iraq', 'envoyconnect'),
				'IE' => __('Republic of Ireland', 'envoyconnect'),
				'IM' => __('Isle of Man', 'envoyconnect'),
				'IL' => __('Israel', 'envoyconnect'),
				'IT' => __('Italy', 'envoyconnect'),
				'CI' => __('Ivory Coast', 'envoyconnect'),
				'JM' => __('Jamaica', 'envoyconnect'),
				'JP' => __('Japan', 'envoyconnect'),
				'JE' => __('Jersey', 'envoyconnect'),
				'JO' => __('Jordan', 'envoyconnect'),
				'KZ' => __('Kazakhstan', 'envoyconnect'),
				'KE' => __('Kenya', 'envoyconnect'),
				'KI' => __('Kiribati', 'envoyconnect'),
				'KW' => __('Kuwait', 'envoyconnect'),
				'KG' => __('Kyrgyzstan', 'envoyconnect'),
				'LA' => __('Laos', 'envoyconnect'),
				'LV' => __('Latvia', 'envoyconnect'),
				'LB' => __('Lebanon', 'envoyconnect'),
				'LS' => __('Lesotho', 'envoyconnect'),
				'LR' => __('Liberia', 'envoyconnect'),
				'LY' => __('Libya', 'envoyconnect'),
				'LI' => __('Liechtenstein', 'envoyconnect'),
				'LT' => __('Lithuania', 'envoyconnect'),
				'LU' => __('Luxembourg', 'envoyconnect'),
				'MO' => __('Macao S.A.R., China', 'envoyconnect'),
				'MK' => __('Macedonia', 'envoyconnect'),
				'MG' => __('Madagascar', 'envoyconnect'),
				'MW' => __('Malawi', 'envoyconnect'),
				'MY' => __('Malaysia', 'envoyconnect'),
				'MV' => __('Maldives', 'envoyconnect'),
				'ML' => __('Mali', 'envoyconnect'),
				'MT' => __('Malta', 'envoyconnect'),
				'MQ' => __('Martinique', 'envoyconnect'),
				'MR' => __('Mauritania', 'envoyconnect'),
				'MU' => __('Mauritius', 'envoyconnect'),
				'YT' => __('Mayotte', 'envoyconnect'),
				'MX' => __('Mexico', 'envoyconnect'),
				'MD' => __('Moldova', 'envoyconnect'),
				'MC' => __('Monaco', 'envoyconnect'),
				'MN' => __('Mongolia', 'envoyconnect'),
				'ME' => __('Montenegro', 'envoyconnect'),
				'MS' => __('Montserrat', 'envoyconnect'),
				'MA' => __('Morocco', 'envoyconnect'),
				'MZ' => __('Mozambique', 'envoyconnect'),
				'MM' => __('Myanmar', 'envoyconnect'),
				'NA' => __('Namibia', 'envoyconnect'),
				'NR' => __('Nauru', 'envoyconnect'),
				'NP' => __('Nepal', 'envoyconnect'),
				'NL' => __('Netherlands', 'envoyconnect'),
				'AN' => __('Netherlands Antilles', 'envoyconnect'),
				'NC' => __('New Caledonia', 'envoyconnect'),
				'NZ' => __('New Zealand', 'envoyconnect'),
				'NI' => __('Nicaragua', 'envoyconnect'),
				'NE' => __('Niger', 'envoyconnect'),
				'NG' => __('Nigeria', 'envoyconnect'),
				'NU' => __('Niue', 'envoyconnect'),
				'NF' => __('Norfolk Island', 'envoyconnect'),
				'KP' => __('North Korea', 'envoyconnect'),
				'MP' => __('Northern Mariana Islands', 'envoyconnect'),
				'NO' => __('Norway', 'envoyconnect'),
				'OM' => __('Oman', 'envoyconnect'),
				'PK' => __('Pakistan', 'envoyconnect'),
				'PW' => __('Palau', 'envoyconnect'),
				'PS' => __('Palestinian Territory', 'envoyconnect'),
				'PA' => __('Panama', 'envoyconnect'),
				'PG' => __('Papua New Guinea', 'envoyconnect'),
				'PY' => __('Paraguay', 'envoyconnect'),
				'PE' => __('Peru', 'envoyconnect'),
				'PH' => __('Philippines', 'envoyconnect'),
				'PN' => __('Pitcairn', 'envoyconnect'),
				'PL' => __('Poland', 'envoyconnect'),
				'PT' => __('Portugal', 'envoyconnect'),
				'QA' => __('Qatar', 'envoyconnect'),
				'RE' => __('Reunion', 'envoyconnect'),
				'RO' => __('Romania', 'envoyconnect'),
				'RU' => __('Russia', 'envoyconnect'),
				'RW' => __('Rwanda', 'envoyconnect'),
				'BL' => __('Saint Barth&eacute;lemy', 'envoyconnect'),
				'SH' => __('Saint Helena', 'envoyconnect'),
				'KN' => __('Saint Kitts and Nevis', 'envoyconnect'),
				'LC' => __('Saint Lucia', 'envoyconnect'),
				'MF' => __('Saint Martin (French part)', 'envoyconnect'),
				'PM' => __('Saint Pierre and Miquelon', 'envoyconnect'),
				'VC' => __('Saint Vincent and the Grenadines', 'envoyconnect'),
				'WS' => __('Samoa', 'envoyconnect'),
				'SM' => __('San Marino', 'envoyconnect'),
				'ST' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'envoyconnect'),
				'SA' => __('Saudi Arabia', 'envoyconnect'),
				'SN' => __('Senegal', 'envoyconnect'),
				'RS' => __('Serbia', 'envoyconnect'),
				'SC' => __('Seychelles', 'envoyconnect'),
				'SL' => __('Sierra Leone', 'envoyconnect'),
				'SG' => __('Singapore', 'envoyconnect'),
				'SK' => __('Slovakia', 'envoyconnect'),
				'SI' => __('Slovenia', 'envoyconnect'),
				'SB' => __('Solomon Islands', 'envoyconnect'),
				'SO' => __('Somalia', 'envoyconnect'),
				'ZA' => __('South Africa', 'envoyconnect'),
				'GS' => __('South Georgia/Sandwich Islands', 'envoyconnect'),
				'KR' => __('South Korea', 'envoyconnect'),
				'ES' => __('Spain', 'envoyconnect'),
				'LK' => __('Sri Lanka', 'envoyconnect'),
				'SD' => __('Sudan', 'envoyconnect'),
				'SR' => __('Suriname', 'envoyconnect'),
				'SJ' => __('Svalbard and Jan Mayen', 'envoyconnect'),
				'SZ' => __('Swaziland', 'envoyconnect'),
				'SE' => __('Sweden', 'envoyconnect'),
				'CH' => __('Switzerland', 'envoyconnect'),
				'SY' => __('Syria', 'envoyconnect'),
				'TW' => __('Taiwan', 'envoyconnect'),
				'TJ' => __('Tajikistan', 'envoyconnect'),
				'TZ' => __('Tanzania', 'envoyconnect'),
				'TH' => __('Thailand', 'envoyconnect'),
				'TL' => __('Timor-Leste', 'envoyconnect'),
				'TG' => __('Togo', 'envoyconnect'),
				'TK' => __('Tokelau', 'envoyconnect'),
				'TO' => __('Tonga', 'envoyconnect'),
				'TT' => __('Trinidad and Tobago', 'envoyconnect'),
				'TN' => __('Tunisia', 'envoyconnect'),
				'TR' => __('Turkey', 'envoyconnect'),
				'TM' => __('Turkmenistan', 'envoyconnect'),
				'TC' => __('Turks and Caicos Islands', 'envoyconnect'),
				'TV' => __('Tuvalu', 'envoyconnect'),
				'UM' => __('US Minor Outlying Islands', 'envoyconnect'),
				'UG' => __('Uganda', 'envoyconnect'),
				'UA' => __('Ukraine', 'envoyconnect'),
				'AE' => __('United Arab Emirates', 'envoyconnect'),
				'GB' => __('United Kingdom', 'envoyconnect'),
				'US' => __('United States', 'envoyconnect'),
				'UY' => __('Uruguay', 'envoyconnect'),
				'UZ' => __('Uzbekistan', 'envoyconnect'),
				'VU' => __('Vanuatu', 'envoyconnect'),
				'VA' => __('Vatican', 'envoyconnect'),
				'VE' => __('Venezuela', 'envoyconnect'),
				'VN' => __('Vietnam', 'envoyconnect'),
				'WF' => __('Wallis and Futuna', 'envoyconnect'),
				'EH' => __('Western Sahara', 'envoyconnect'),
				'YE' => __('Yemen', 'envoyconnect'),
				'ZM' => __('Zambia', 'envoyconnect'),
				'ZW' => __('Zimbabwe', 'envoyconnect'),
	) );
	
}


function envoyconnect_dropdown_roles( $selected = false ) {
	$p = '';
	$r = '';

	$editable_roles = get_editable_roles();

	foreach ( $editable_roles as $role => $details ) {
		$name = translate_user_role( $details['name'] );
		if ( is_array( $selected ) ) {
			if ( in_array( $role, $selected ) ) // preselect specified role
				$p .= "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
			else
				$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
		} else {
			if ( $selected == $role ) // preselect specified role
				$p = "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
			else
				$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
		}
	}
	echo $p . $r;
}


function envoyconnect_get_days() {
	return array( '01' => '1', '02' => '2', '03' => '3', '04' => '4', '05' => '5', '06' => '6', '07' => '7', '08' => '8', '09' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20', '21' => '21', '22' => '22', '23' => '23', '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31' );
}

function envoyconnect_get_months() {
	return array( 
					'01' => __( 'January' ), 
					'02' => __( 'February' ), 
					'03' => __( 'March' ), 
					'04' => __( 'April' ), 
					'05' => __( 'May' ), 
					'06' => __( 'June' ), 
					'07' => __( 'July' ), 
					'08' => __( 'August' ), 
					'09' => __( 'September' ), 
					'10' => __( 'October' ), 
					'11' => __( 'November' ), 
					'12' => __( 'December' ),  
	);
}