<?php

function bbconnect_helper_state() {
	$states = apply_filters( 'bbconnect_helper_state', bbconnect_get_helper_states() );
	$countries = bbconnect_helper_country();
	foreach ( $states as $key => $val ) {
		if ( isset( $countries[$key] ) )
			$new_states[$countries[$key]] = $val;
	}
	return $new_states;

}

function bbconnect_helper_segment() {
    return bbconnect_get_helper_saved_searches('segment');
}

function bbconnect_helper_category() {
    return bbconnect_get_helper_saved_searches('category');
}

function bbconnect_get_helper_saved_searches($search_type) {
    $args = array(
            'post_type' => 'savedsearch',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'post_title',
            'order' => 'DESC',
            'meta_query' => array(
                    array(
                            'key' => $search_type,
                            'value' => 'true'
                    )
            )
    );
    $searches = get_posts($args);

    $result = array();
    foreach ($searches as $key => $search) {
        $result[$search->ID] = $search->post_title;
    }

    return $result;
}

function bbconnect_merge_helper_locs() {
	$states = apply_filters( 'bbconnect_merge_helper_states', bbconnect_get_helper_states() );
	$countries = bbconnect_helper_country();
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

function bbconnect_get_helper_states() {

	return apply_filters( 'bbconnect_get_helper_states', array(
		'AU' => array(
			'ACT' => __('Australian Capital Territory', 'bbconnect'),
			'NSW' => __('New South Wales', 'bbconnect'),
			'NT' => __('Northern Territory', 'bbconnect'),
			'QLD' => __('Queensland', 'bbconnect'),
			'SA' => __('South Australia', 'bbconnect'),
			'TAS' => __('Tasmania', 'bbconnect'),
			'VIC' => __('Victoria', 'bbconnect'),
			'WA' => __('Western Australia', 'bbconnect'),
		),
		'CA' => array(
			'AB' => __('Alberta', 'bbconnect'),
			'BC' => __('British Columbia', 'bbconnect'),
			'MB' => __('Manitoba', 'bbconnect'),
			'NB' => __('New Brunswick', 'bbconnect'),
			'NF' => __('Newfoundland', 'bbconnect'),
			'NT' => __('Northwest Territories', 'bbconnect'),
			'NS' => __('Nova Scotia', 'bbconnect'),
			'NU' => __('Nunavut', 'bbconnect'),
			'ON' => __('Ontario', 'bbconnect'),
			'PE' => __('Prince Edward Island', 'bbconnect'),
			'QC' => __('Quebec', 'bbconnect'),
			'SK' => __('Saskatchewan', 'bbconnect'),
			'YT' => __('Yukon Territory', 'bbconnect'),
		),
		'US' => array(
			'AL' => __('Alabama', 'bbconnect'),
			'AK' => __('Alaska', 'bbconnect'),
			'AZ' => __('Arizona', 'bbconnect'),
			'AR' => __('Arkansas', 'bbconnect'),
			'CA' => __('California', 'bbconnect'),
			'CO' => __('Colorado', 'bbconnect'),
			'CT' => __('Connecticut', 'bbconnect'),
			'DE' => __('Delaware', 'bbconnect'),
			'DC' => __('District Of Columbia', 'bbconnect'),
			'FL' => __('Florida', 'bbconnect'),
			'GA' => __('Georgia', 'bbconnect'),
			'HI' => __('Hawaii', 'bbconnect'),
			'ID' => __('Idaho', 'bbconnect'),
			'IL' => __('Illinois', 'bbconnect'),
			'IN' => __('Indiana', 'bbconnect'),
			'IA' => __('Iowa', 'bbconnect'),
			'KS' => __('Kansas', 'bbconnect'),
			'KY' => __('Kentucky', 'bbconnect'),
			'LA' => __('Louisiana', 'bbconnect'),
			'ME' => __('Maine', 'bbconnect'),
			'MD' => __('Maryland', 'bbconnect'),
			'MA' => __('Massachusetts', 'bbconnect'),
			'MI' => __('Michigan', 'bbconnect'),
			'MN' => __('Minnesota', 'bbconnect'),
			'MS' => __('Mississippi', 'bbconnect'),
			'MO' => __('Missouri', 'bbconnect'),
			'MT' => __('Montana', 'bbconnect'),
			'NE' => __('Nebraska', 'bbconnect'),
			'NV' => __('Nevada', 'bbconnect'),
			'NH' => __('New Hampshire', 'bbconnect'),
			'NJ' => __('New Jersey', 'bbconnect'),
			'NM' => __('New Mexico', 'bbconnect'),
			'NY' => __('New York', 'bbconnect'),
			'NC' => __('North Carolina', 'bbconnect'),
			'ND' => __('North Dakota', 'bbconnect'),
			'OH' => __('Ohio', 'bbconnect'),
			'OK' => __('Oklahoma', 'bbconnect'),
			'OR' => __('Oregon', 'bbconnect'),
			'PA' => __('Pennsylvania', 'bbconnect'),
			'RI' => __('Rhode Island', 'bbconnect'),
			'SC' => __('South Carolina', 'bbconnect'),
			'SD' => __('South Dakota', 'bbconnect'),
			'TN' => __('Tennessee', 'bbconnect'),
			'TX' => __('Texas', 'bbconnect'),
			'UT' => __('Utah', 'bbconnect'),
			'VT' => __('Vermont', 'bbconnect'),
			'VA' => __('Virginia', 'bbconnect'),
			'WA' => __('Washington', 'bbconnect'),
			'WV' => __('West Virginia', 'bbconnect'),
			'WI' => __('Wisconsin', 'bbconnect'),
			'WY' => __('Wyoming', 'bbconnect'),
			'AA' => __('Armed Forces Americas', 'bbconnect'),
			'AE' => __('Armed Forces Europe', 'bbconnect'),
			'AP' => __('Armed Forces Pacific', 'bbconnect'),
			'AS' => __('American Samoa', 'bbconnect'),
			'FM' => __('Micronesia', 'bbconnect'),
			'GU' => __('Guam', 'bbconnect'),
			'MH' => __('Marshall Islands', 'bbconnect'),
			'PR' => __('Puerto Rico', 'bbconnect'),
			'VI' => __('U.S. Virgin Islands', 'bbconnect'),
		),
	) );

}

function bbconnect_helper_country() {

	return apply_filters( 'bbconnect_helper_country', array(
				'AF' => __('Afghanistan', 'bbconnect'),
				'AX' => __('&#197;land Islands', 'bbconnect'),
				'AL' => __('Albania', 'bbconnect'),
				'DZ' => __('Algeria', 'bbconnect'),
				'AD' => __('Andorra', 'bbconnect'),
				'AO' => __('Angola', 'bbconnect'),
				'AI' => __('Anguilla', 'bbconnect'),
				'AQ' => __('Antarctica', 'bbconnect'),
				'AG' => __('Antigua and Barbuda', 'bbconnect'),
				'AR' => __('Argentina', 'bbconnect'),
				'AM' => __('Armenia', 'bbconnect'),
				'AW' => __('Aruba', 'bbconnect'),
				'AU' => __('Australia', 'bbconnect'),
				'AT' => __('Austria', 'bbconnect'),
				'AZ' => __('Azerbaijan', 'bbconnect'),
				'BS' => __('Bahamas', 'bbconnect'),
				'BH' => __('Bahrain', 'bbconnect'),
				'BD' => __('Bangladesh', 'bbconnect'),
				'BB' => __('Barbados', 'bbconnect'),
				'BY' => __('Belarus', 'bbconnect'),
				'BE' => __('Belgium', 'bbconnect'),
				'BZ' => __('Belize', 'bbconnect'),
				'BJ' => __('Benin', 'bbconnect'),
				'BM' => __('Bermuda', 'bbconnect'),
				'BT' => __('Bhutan', 'bbconnect'),
				'BO' => __('Bolivia', 'bbconnect'),
				'BA' => __('Bosnia and Herzegovina', 'bbconnect'),
				'BW' => __('Botswana', 'bbconnect'),
				'BR' => __('Brazil', 'bbconnect'),
				'IO' => __('British Indian Ocean Territory', 'bbconnect'),
				'VG' => __('British Virgin Islands', 'bbconnect'),
				'BN' => __('Brunei', 'bbconnect'),
				'BG' => __('Bulgaria', 'bbconnect'),
				'BF' => __('Burkina Faso', 'bbconnect'),
				'BI' => __('Burundi', 'bbconnect'),
				'KH' => __('Cambodia', 'bbconnect'),
				'CM' => __('Cameroon', 'bbconnect'),
				'CA' => __('Canada', 'bbconnect'),
				'CV' => __('Cape Verde', 'bbconnect'),
				'KY' => __('Cayman Islands', 'bbconnect'),
				'CF' => __('Central African Republic', 'bbconnect'),
				'TD' => __('Chad', 'bbconnect'),
				'CL' => __('Chile', 'bbconnect'),
				'CN' => __('China', 'bbconnect'),
				'CX' => __('Christmas Island', 'bbconnect'),
				'CC' => __('Cocos (Keeling) Islands', 'bbconnect'),
				'CO' => __('Colombia', 'bbconnect'),
				'KM' => __('Comoros', 'bbconnect'),
				'CG' => __('Congo (Brazzaville)', 'bbconnect'),
				'CD' => __('Congo (Kinshasa)', 'bbconnect'),
				'CK' => __('Cook Islands', 'bbconnect'),
				'CR' => __('Costa Rica', 'bbconnect'),
				'HR' => __('Croatia', 'bbconnect'),
				'CU' => __('Cuba', 'bbconnect'),
				'CY' => __('Cyprus', 'bbconnect'),
				'CZ' => __('Czech Republic', 'bbconnect'),
				'DK' => __('Denmark', 'bbconnect'),
				'DJ' => __('Djibouti', 'bbconnect'),
				'DM' => __('Dominica', 'bbconnect'),
				'DO' => __('Dominican Republic', 'bbconnect'),
				'EC' => __('Ecuador', 'bbconnect'),
				'EG' => __('Egypt', 'bbconnect'),
				'SV' => __('El Salvador', 'bbconnect'),
				'GQ' => __('Equatorial Guinea', 'bbconnect'),
				'ER' => __('Eritrea', 'bbconnect'),
				'EE' => __('Estonia', 'bbconnect'),
				'ET' => __('Ethiopia', 'bbconnect'),
				'FK' => __('Falkland Islands', 'bbconnect'),
				'FO' => __('Faroe Islands', 'bbconnect'),
				'FJ' => __('Fiji', 'bbconnect'),
				'FI' => __('Finland', 'bbconnect'),
				'FR' => __('France', 'bbconnect'),
				'GF' => __('French Guiana', 'bbconnect'),
				'PF' => __('French Polynesia', 'bbconnect'),
				'TF' => __('French Southern Territories', 'bbconnect'),
				'GA' => __('Gabon', 'bbconnect'),
				'GM' => __('Gambia', 'bbconnect'),
				'GE' => __('Georgia', 'bbconnect'),
				'DE' => __('Germany', 'bbconnect'),
				'GH' => __('Ghana', 'bbconnect'),
				'GI' => __('Gibraltar', 'bbconnect'),
				'GR' => __('Greece', 'bbconnect'),
				'GL' => __('Greenland', 'bbconnect'),
				'GD' => __('Grenada', 'bbconnect'),
				'GP' => __('Guadeloupe', 'bbconnect'),
				'GT' => __('Guatemala', 'bbconnect'),
				'GG' => __('Guernsey', 'bbconnect'),
				'GN' => __('Guinea', 'bbconnect'),
				'GW' => __('Guinea-Bissau', 'bbconnect'),
				'GY' => __('Guyana', 'bbconnect'),
				'HT' => __('Haiti', 'bbconnect'),
				'HN' => __('Honduras', 'bbconnect'),
				'HK' => __('Hong Kong', 'bbconnect'),
				'HU' => __('Hungary', 'bbconnect'),
				'IS' => __('Iceland', 'bbconnect'),
				'IN' => __('India', 'bbconnect'),
				'ID' => __('Indonesia', 'bbconnect'),
				'IR' => __('Iran', 'bbconnect'),
				'IQ' => __('Iraq', 'bbconnect'),
				'IE' => __('Republic of Ireland', 'bbconnect'),
				'IM' => __('Isle of Man', 'bbconnect'),
				'IL' => __('Israel', 'bbconnect'),
				'IT' => __('Italy', 'bbconnect'),
				'CI' => __('Ivory Coast', 'bbconnect'),
				'JM' => __('Jamaica', 'bbconnect'),
				'JP' => __('Japan', 'bbconnect'),
				'JE' => __('Jersey', 'bbconnect'),
				'JO' => __('Jordan', 'bbconnect'),
				'KZ' => __('Kazakhstan', 'bbconnect'),
				'KE' => __('Kenya', 'bbconnect'),
				'KI' => __('Kiribati', 'bbconnect'),
				'KW' => __('Kuwait', 'bbconnect'),
				'KG' => __('Kyrgyzstan', 'bbconnect'),
				'LA' => __('Laos', 'bbconnect'),
				'LV' => __('Latvia', 'bbconnect'),
				'LB' => __('Lebanon', 'bbconnect'),
				'LS' => __('Lesotho', 'bbconnect'),
				'LR' => __('Liberia', 'bbconnect'),
				'LY' => __('Libya', 'bbconnect'),
				'LI' => __('Liechtenstein', 'bbconnect'),
				'LT' => __('Lithuania', 'bbconnect'),
				'LU' => __('Luxembourg', 'bbconnect'),
				'MO' => __('Macao S.A.R., China', 'bbconnect'),
				'MK' => __('Macedonia', 'bbconnect'),
				'MG' => __('Madagascar', 'bbconnect'),
				'MW' => __('Malawi', 'bbconnect'),
				'MY' => __('Malaysia', 'bbconnect'),
				'MV' => __('Maldives', 'bbconnect'),
				'ML' => __('Mali', 'bbconnect'),
				'MT' => __('Malta', 'bbconnect'),
				'MQ' => __('Martinique', 'bbconnect'),
				'MR' => __('Mauritania', 'bbconnect'),
				'MU' => __('Mauritius', 'bbconnect'),
				'YT' => __('Mayotte', 'bbconnect'),
				'MX' => __('Mexico', 'bbconnect'),
				'MD' => __('Moldova', 'bbconnect'),
				'MC' => __('Monaco', 'bbconnect'),
				'MN' => __('Mongolia', 'bbconnect'),
				'ME' => __('Montenegro', 'bbconnect'),
				'MS' => __('Montserrat', 'bbconnect'),
				'MA' => __('Morocco', 'bbconnect'),
				'MZ' => __('Mozambique', 'bbconnect'),
				'MM' => __('Myanmar', 'bbconnect'),
				'NA' => __('Namibia', 'bbconnect'),
				'NR' => __('Nauru', 'bbconnect'),
				'NP' => __('Nepal', 'bbconnect'),
				'NL' => __('Netherlands', 'bbconnect'),
				'AN' => __('Netherlands Antilles', 'bbconnect'),
				'NC' => __('New Caledonia', 'bbconnect'),
				'NZ' => __('New Zealand', 'bbconnect'),
				'NI' => __('Nicaragua', 'bbconnect'),
				'NE' => __('Niger', 'bbconnect'),
				'NG' => __('Nigeria', 'bbconnect'),
				'NU' => __('Niue', 'bbconnect'),
				'NF' => __('Norfolk Island', 'bbconnect'),
				'KP' => __('North Korea', 'bbconnect'),
				'MP' => __('Northern Mariana Islands', 'bbconnect'),
				'NO' => __('Norway', 'bbconnect'),
				'OM' => __('Oman', 'bbconnect'),
				'PK' => __('Pakistan', 'bbconnect'),
				'PW' => __('Palau', 'bbconnect'),
				'PS' => __('Palestinian Territory', 'bbconnect'),
				'PA' => __('Panama', 'bbconnect'),
				'PG' => __('Papua New Guinea', 'bbconnect'),
				'PY' => __('Paraguay', 'bbconnect'),
				'PE' => __('Peru', 'bbconnect'),
				'PH' => __('Philippines', 'bbconnect'),
				'PN' => __('Pitcairn', 'bbconnect'),
				'PL' => __('Poland', 'bbconnect'),
				'PT' => __('Portugal', 'bbconnect'),
				'QA' => __('Qatar', 'bbconnect'),
				'RE' => __('Reunion', 'bbconnect'),
				'RO' => __('Romania', 'bbconnect'),
				'RU' => __('Russia', 'bbconnect'),
				'RW' => __('Rwanda', 'bbconnect'),
				'BL' => __('Saint Barth&eacute;lemy', 'bbconnect'),
				'SH' => __('Saint Helena', 'bbconnect'),
				'KN' => __('Saint Kitts and Nevis', 'bbconnect'),
				'LC' => __('Saint Lucia', 'bbconnect'),
				'MF' => __('Saint Martin (French part)', 'bbconnect'),
				'PM' => __('Saint Pierre and Miquelon', 'bbconnect'),
				'VC' => __('Saint Vincent and the Grenadines', 'bbconnect'),
				'WS' => __('Samoa', 'bbconnect'),
				'SM' => __('San Marino', 'bbconnect'),
				'ST' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'bbconnect'),
				'SA' => __('Saudi Arabia', 'bbconnect'),
				'SN' => __('Senegal', 'bbconnect'),
				'RS' => __('Serbia', 'bbconnect'),
				'SC' => __('Seychelles', 'bbconnect'),
				'SL' => __('Sierra Leone', 'bbconnect'),
				'SG' => __('Singapore', 'bbconnect'),
				'SK' => __('Slovakia', 'bbconnect'),
				'SI' => __('Slovenia', 'bbconnect'),
				'SB' => __('Solomon Islands', 'bbconnect'),
				'SO' => __('Somalia', 'bbconnect'),
				'ZA' => __('South Africa', 'bbconnect'),
				'GS' => __('South Georgia/Sandwich Islands', 'bbconnect'),
				'KR' => __('South Korea', 'bbconnect'),
				'ES' => __('Spain', 'bbconnect'),
				'LK' => __('Sri Lanka', 'bbconnect'),
				'SD' => __('Sudan', 'bbconnect'),
				'SR' => __('Suriname', 'bbconnect'),
				'SJ' => __('Svalbard and Jan Mayen', 'bbconnect'),
				'SZ' => __('Swaziland', 'bbconnect'),
				'SE' => __('Sweden', 'bbconnect'),
				'CH' => __('Switzerland', 'bbconnect'),
				'SY' => __('Syria', 'bbconnect'),
				'TW' => __('Taiwan', 'bbconnect'),
				'TJ' => __('Tajikistan', 'bbconnect'),
				'TZ' => __('Tanzania', 'bbconnect'),
				'TH' => __('Thailand', 'bbconnect'),
				'TL' => __('Timor-Leste', 'bbconnect'),
				'TG' => __('Togo', 'bbconnect'),
				'TK' => __('Tokelau', 'bbconnect'),
				'TO' => __('Tonga', 'bbconnect'),
				'TT' => __('Trinidad and Tobago', 'bbconnect'),
				'TN' => __('Tunisia', 'bbconnect'),
				'TR' => __('Turkey', 'bbconnect'),
				'TM' => __('Turkmenistan', 'bbconnect'),
				'TC' => __('Turks and Caicos Islands', 'bbconnect'),
				'TV' => __('Tuvalu', 'bbconnect'),
				'UM' => __('US Minor Outlying Islands', 'bbconnect'),
				'UG' => __('Uganda', 'bbconnect'),
				'UA' => __('Ukraine', 'bbconnect'),
				'AE' => __('United Arab Emirates', 'bbconnect'),
				'GB' => __('United Kingdom', 'bbconnect'),
				'US' => __('United States', 'bbconnect'),
				'UY' => __('Uruguay', 'bbconnect'),
				'UZ' => __('Uzbekistan', 'bbconnect'),
				'VU' => __('Vanuatu', 'bbconnect'),
				'VA' => __('Vatican', 'bbconnect'),
				'VE' => __('Venezuela', 'bbconnect'),
				'VN' => __('Vietnam', 'bbconnect'),
				'WF' => __('Wallis and Futuna', 'bbconnect'),
				'EH' => __('Western Sahara', 'bbconnect'),
				'YE' => __('Yemen', 'bbconnect'),
				'ZM' => __('Zambia', 'bbconnect'),
				'ZW' => __('Zimbabwe', 'bbconnect'),
	) );

}


function bbconnect_dropdown_roles( $selected = false ) {
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
				$p .= "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
			else
				$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
		}
	}
	echo $p . $r;
}


function bbconnect_get_days() {
	return array( '01' => '1', '02' => '2', '03' => '3', '04' => '4', '05' => '5', '06' => '6', '07' => '7', '08' => '8', '09' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20', '21' => '21', '22' => '22', '23' => '23', '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31' );
}

function bbconnect_get_months() {
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