<?php
// define('WP_DISABLED_FUNCTIONS', 'request,remove_emoji');

function sgtcoder_is_valid_function($function_name)
{
	$disabled_functions = defined('WP_DISABLED_FUNCTIONS') ? explode(',', WP_DISABLED_FUNCTIONS) : [];

	if (in_array($function_name, $disabled_functions)) {
		return false;
	}

	if (function_exists($function_name)) {
		return false;
	}

	return true;
}

if (sgtcoder_is_valid_function('remove_emoji')) {
	function remove_emoji($text)
	{
		return preg_replace('/[[:^print:]]/', '', $text);
	}
}

if (sgtcoder_is_valid_function('ascii_only')) {
	function ascii_only($text)
	{
		return preg_replace('/[[:^print:]]/', '', $text);
	}
}

if (sgtcoder_is_valid_function('bulk_insert')) {
	function bulk_insert($table, $array, $ascii_only = true)
	{
		global $wpdb;

		$query = "INSERT INTO " . $wpdb->prefix . $table . " (" . implode(',', array_keys(reset($array))) . ") VALUES ";

		$array_values = array();
		foreach ($array as $key => $row) {
			if ($ascii_only) {
				$keys = array_keys($row);

				foreach ($keys as $key) {
					$row[$key] = ascii_only($row[$key]);
				}
			}

			$array_values[] = '("' . implode('","', array_values($row)) . '")';
		}

		$query .= implode(',', $array_values);

		$wpdb->query($query);
	}
}

if (sgtcoder_is_valid_function('get_user_role')) {
	function get_user_role()
	{
		global $wp_roles;

		$current_user = wp_get_current_user();
		$roles = $current_user->roles;
		$role = array_shift($roles);

		return $role;
	}
}

if (sgtcoder_is_valid_function('is_cli_running')) {
	function is_cli_running()
	{
		return defined('WP_CLI') && WP_CLI;
	}
}

if (sgtcoder_is_valid_function('cli_log')) {
	function cli_log($title, $message, $force_plain = false)
	{
		date_default_timezone_set('UTC');

		$line = '[' . date("Y-m-d H:i:s") . '] ' . $title . ': ' . $message;

		if ($force_plain) {
			echo $line . PHP_EOL;
		} else {
			if (is_cli_running()) {
				WP_CLI::line($line);
			} else {
				echo $line . PHP_EOL . '<br />';
			}
		}
	}
}

if (sgtcoder_is_valid_function('addslashes_array')) {
	function addslashes_array($array)
	{
		foreach ($array as $key => $value) {
			$array[$key] = addslashes($value);
		}

		return $array;
	}
}

if (sgtcoder_is_valid_function('get_domain')) {
	function get_domain()
	{
		$domain = parse_url(get_site_url(), PHP_URL_HOST);

		return $domain;
	}
}

if (sgtcoder_is_valid_function('file_cache_ver')) {
	function file_cache_ver($file, $theme = true, $prefix = true)
	{
		$filepath = '';

		if ($theme) {
			$filepath = get_stylesheet_directory() . '/' . $file;
		} else {
			$filepath = $file;
		}

		$time = filemtime($filepath);

		if ($prefix) $time = '?ver=' . $time;

		return $time;
	}
}

if (sgtcoder_is_valid_function('build_the_query')) {
	function build_the_query($array, $trim = false)
	{
		$query_array = array();

		foreach ($array as $field => $value) {
			if ($trim) $value = trim($value);
			$query_array[] = $field . '="' . $value . '"';
		}

		$query_array = implode(",", $query_array);

		return $query_array;
	}
}

if (sgtcoder_is_valid_function('query_builder')) {
	function query_builder($array = array(), $table = "", $action = "", $where = "", $exclude = array(), $execute = false, $update_field = "")
	{

		global $wpdb;

		if (strpos($table, $wpdb->prefix) === FALSE && strpos($table, 'wp_') === FALSE) $table = $wpdb->prefix . $table;

		if (empty($exclude)) $exclude = array('option_page', 'action', '_wpnonce', '_wp_http_referer', 'submit');

		$query_array = array();
		$data_return = array();

		foreach ($array as $field => $value) {
			if (!in_array($field, $exclude)) {
				if ($value == "") {
					$value = "NULL";
				} else {
					$value = '"' . esc_sql($value) . '"';
				}

				$query_array[] = $field . "=" . $value;
			}
		}

		$query_array = implode(",", $query_array);

		if (!empty($where)) {
			$where = " WHERE " . $where;
		}

		$the_query = $action . ' ' . $table . ' SET ' . $query_array . $where;

		//echo $the_query;exit;

		$data_return['data']['query'] = $the_query;

		if ($execute) {
			$query_result = $wpdb->query($the_query);

			$data_return['data']['id'] = $wpdb->insert_id;

			if (!empty($update_field)) {
				$update_field_query = 'UPDATE ' . $table . ' SET ' . $update_field . '=' . $data_return['data']['id'] . ' WHERE id=' . $data_return['data']['id'];

				$query_result = $wpdb->query($update_field_query);
			}
		}

		$data_return['status'] = true;
		$data_return['message'] = "Updated Successfully";

		return $data_return;
	}
}

if (sgtcoder_is_valid_function('basedomain')) {
	function basedomain()
	{
		$url = site_url();

		$url = @parse_url($url);
		if (empty($url['host'])) return;
		$parts = explode('.', $url['host']);
		$slice = (strlen(reset(array_slice($parts, -2, 1))) == 2) && (count($parts) > 2) ? 3 : 2;
		return implode('.', array_slice($parts, (0 - $slice), $slice));
	}
}

if (sgtcoder_is_valid_function('log_sync_error')) {
	function log_sync_error($type, $message)
	{
		// UTC Time
		date_default_timezone_set('UTC');

		global $wpdb;

		$query = "
		    INSERT INTO
			    " . $wpdb->prefix . "error_log
		    SET
			    type='" . $type . "',
			    message='" . addslashes($message) . "',
			    datetime='" . date('Y-m-d H:i:s') . "'
		    ";

		$wpdb->query($query);
	}
}

if (sgtcoder_is_valid_function('validateDate')) {
	function validateDate($date, $format = 'Y-m-d H:i:s')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
}

if (sgtcoder_is_valid_function('print_r2')) {
	function print_r2($array)
	{
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
}

if (sgtcoder_is_valid_function('dd')) {
	function dd($array)
	{
		print_r2($array);

		exit;
	}
}

if (sgtcoder_is_valid_function('convertDateTimeLocal')) {
	function convertDateTimeLocal($datetime, $timezone = "")
	{
		if (empty($datetime)) return $datetime;
		if (empty($timezone)) $timezone = get_option('timezone_string');

		$datetime = new DateTime($datetime);
		$timezone = new DateTimeZone($timezone);

		$datetime->setTimezone($timezone);

		$datetime = $datetime->format('Y-m-d H:i:s');

		return $datetime;
	}
}

if (sgtcoder_is_valid_function('convertDateTimeUTC')) {
	function convertDateTimeUTC($datetime, $timezone = "")
	{
		if (empty($datetime)) return $datetime;
		if (empty($timezone)) $timezone = get_option('timezone_string');

		date_default_timezone_set($timezone);

		$datetime = new DateTime($datetime);
		$timezone = new DateTimeZone('UTC');

		$datetime->setTimezone($timezone);

		$datetime = $datetime->format('Y-m-d H:i:s');

		return $datetime;
	}
}

if (sgtcoder_is_valid_function('getDateTimeFromDates')) {
	function getDateTimeFromDates($start, $finish)
	{
		if (empty($start) || empty($finish)) return '-';

		$datetime1 = strtotime($start);
		$datetime2 = strtotime($finish);
		$interval  = abs($datetime2 - $datetime1);
		$minutes   = number_format(($interval / 60), 2);

		return $minutes . "mins";
	}
}

if (sgtcoder_is_valid_function('xmlToArray')) {
	function xmlToArray($path)
	{
		$xmlfile = file_get_contents($path);
		$ob = simplexml_load_string($xmlfile);
		$json  = json_encode($ob);
		$configData = json_decode($json, true);

		return $configData;
	}
}

if (sgtcoder_is_valid_function('updateCronMeta')) {
	function updateCronMeta($data_array, $table, $insert_array = array())
	{
		global $wpdb;

		foreach ($data_array as $key => $value) {
			$update_query = build_the_query($value);

			$insert_query = build_the_query($insert_array);
			if (!empty($insert_query)) $insert_query = "," . $insert_query;

			$query = "INSERT INTO
	                    " . $table . "
	                    SET
	                        " . $update_query . $insert_query . "
	                    ON DUPLICATE KEY UPDATE
	                    " . $update_query . "
	                ";

			$wpdb->query($query);
		}
	}
}

if (sgtcoder_is_valid_function('getLatestId')) {
	function getLatestId($table, $column, $ai = true, $add_prefix = false)
	{
		global $wpdb;

		if ($add_prefix) $table = $wpdb->prefix . $table;

		$query = '
            SELECT
                MAX(' . $column . ')
            FROM
                ' . $table . '
            LIMIT 1
        ';

		$id = $wpdb->get_var($query);

		if ($ai) $id++;

		return $id;
	}
}

if (sgtcoder_is_valid_function('getStatusCode')) {
	function getStatusCode($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
		curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return  $httpcode;
	}
}

if (sgtcoder_is_valid_function('validStatusCode')) {
	function validStatusCode($url, $valid_codes = array(200, 301, 302))
	{
		$http_code = getStatusCode($url);

		if (in_array($http_code, $valid_codes)) {
			return true;
		} else {
			return false;
		}
	}
}

if (sgtcoder_is_valid_function('get_arg_string')) {
	function get_arg_string($args)
	{
		$arg_array = array();

		foreach ($args as $key => $value) {
			$arg_array[] = '--' . $key . '=' . $value;
		}

		$arg_string = implode(' ', $arg_array);

		return $arg_string;
	}
}

if (sgtcoder_is_valid_function('get_env_string')) {
	function get_env_string($env)
	{
		$env_array = array();

		foreach ($env as $key => $value) {
			$env_array[] = $key . '=' . $value;
		}

		$env_string = implode(' ', $env_array);

		return $env_string;
	}
}

if (sgtcoder_is_valid_function('array_map_assoc')) {
	function array_map_assoc($func, $ar)
	{
		$rv = array();
		foreach ($ar as $key => $val) {
			$func($key, $val);
			$rv[$key] = $val;
		}
		return $rv;
	}
}

if (sgtcoder_is_valid_function('get_content')) {
	function get_content($url)
	{
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 2,
			)
		);

		if (is_array($response) && !is_wp_error($response)) {
			return $response['body'];
		} else {
			return '';
		}
	}
}

if (sgtcoder_is_valid_function('save_content')) {
	function save_content($url, $path)
	{
		if (is_array($response) && !is_wp_error($response) && $response['body']) {
			file_put_contents($path, $response['body']);

			return true;
		} else {
			return false;
		}
	}
}

if (sgtcoder_is_valid_function('get_timezone_string')) {
	function get_timezone_string()
	{
		$timezone = get_option('timezone_string');
		$datetime = new DateTime(date('Y-m-d'));
		$timezone = new DateTimeZone($timezone);

		$datetime->setTimezone($timezone);

		$datetime = $datetime->format('T');

		return $datetime;
	}
}

if (sgtcoder_is_valid_function('request')) {
	function request($param)
	{
		return $_GET[$param] ?? $_POST[$param] ?? NULL;
	}
}

if (sgtcoder_is_valid_function('stripslashes_array')) {
	function stripslashes_array($array, $htmlentities = false)
	{
		// Check if the parameter is an array
		if (is_array($array)) {
			// Loop through the initial dimension
			foreach ($array as $key => $value) {
				// Check if any nodes are arrays themselves
				if (is_array($array[$key]))
					// If they are, let the function call itself over that particular node
					$array[$key] = stripslashes_array($array[$key]);

				// Check if the nodes are strings
				if (is_string($array[$key]))
					// If they are, perform the real escape function over the selected node
					$array[$key] = stripslashes($array[$key]);
				if ($htmlentities) $array[$key] = htmlentities($array[$key]);
			}
		}
		// Check if the parameter is a string
		if (is_string($array))
			// If it is, perform a  mysql_real_escape_string on the parameter
			$array = stripslashes($array);
		if ($htmlentities) $array[$key] = htmlentities($array[$key]);

		// Return the filtered result

		return $array;
	}
}
