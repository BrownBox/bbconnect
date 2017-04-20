<?php
/**
 * Returns the timezone string for a site, even if it's set to a UTC offset
 *
 * Taken from https://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
 *
 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
 *
 * @return string valid PHP timezone string
 */
function bbconnect_get_timezone_string() {
    // if site timezone string exists, return it
    if ($timezone = get_option('timezone_string')) {
        return $timezone;
    }

        // get UTC offset, if it isn't set then return UTC
    if (0 === ($utc_offset = get_option('gmt_offset', 0))) {
        return 'UTC';
    }

        // adjust UTC offset from hours to seconds
    $utc_offset *= 3600;

    // attempt to guess the timezone string from the UTC offset
    if ($timezone = timezone_name_from_abbr('', $utc_offset, 0)) {
        return $timezone;
    }

    // last try, guess timezone string manually
    $is_dst = date('I');

    foreach (timezone_abbreviations_list() as $abbr) {
        foreach ($abbr as $city) {
            if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset) {
                return $city['timezone_id'];
            }
        }
    }

    // fallback to UTC
    return 'UTC';
}

/**
 * Get DateTimeZone object for current site
 * @param string $timezone_str
 * @return DateTimeZone
 */
function bbconnect_get_timezone($timezone_str = '') {
    if (empty($timezone_str)) {
        $timezone_str = bbconnect_get_timezone_string();
    }
    return new DateTimeZone($timezone_str);
}

function bbconnect_get_current_datetime(DateTimeZone $timezone = null) {
    if (is_null($timezone)) {
        $timezone = bbconnect_get_timezone();
    }
    return new DateTime('now', $timezone);
}

function bbconnect_get_datetime($datetime = '', DateTimeZone $timezone = null) {
    if (empty($datetime)) {
        return bbconnect_get_current_datetime($timezone);
    }

    if (is_int($datetime)) {
        $datetime = '@'.$datetime;
    }

    if (is_null($timezone)) {
        $timezone = bbconnect_get_timezone();
    }
    return new DateTime($datetime, $timezone);
}
