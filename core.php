<?php
/**
 *
 * @ This file is created by DeZend.Org
 * @ DeZend (PHP5 Decoder for ionCube Encoder)
 *
 * @	Version			:	1.1.7.0
 * @	Author			:	TuhanTS
 * @	Release on		:	25.02.2013
 * @	Official site	:	http://DeZend.Org
 *
 */

class spbas {
	var $errors = null;
	var $license_key = null;
	var $api_server = null;
	var $remote_port = null;
	var $remote_timeout = null;
	var $local_key_storage = null;
	var $read_query = null;
	var $update_query = null;
	var $local_key_path = null;
	var $local_key_name = null;
	var $local_key_transport_order = null;
	var $local_key_grace_period = null;
	var $local_key_last = null;
	var $validate_download_access = null;
	var $release_date = null;
	var $key_data = null;
	var $status_messages = null;
	var $valid_for_product_tiers = null;

	function spbas() {
		$this->errors = false;
		$this->remote_port = 80;
		$this->remote_timeout = 10;
		$this->valid_local_key_types = array( 'spbas' );
		$this->local_key_type = 'spbas';
		$this->local_key_storage = 'filesystem';
		$this->local_key_grace_period = 0;
		$this->local_key_last = 0;
		$this->read_query = false;
		$this->update_query = false;
		$this->local_key_path = './';
		$this->local_key_name = 'license.txt';
		$this->local_key_transport_order = 'csf';
		$this->validate_download_access = false;
		$this->release_date = false;
		$this->valid_for_product_tiers = false;
		$this->key_data = array( 'custom_fields' => array(  ), 'download_access_expires' => 0, 'license_expires' => 0, 'local_key_expires' => 0, 'status' => 'Invalid' );
		$this->status_messages = array( 'active' => 'This license is active.', 'suspended' => 'Error: This license has been suspended.', 'expired' => 'Error: This license has expired.', 'pending' => 'Error: This license is pending review.', 'download_access_expired' => 'Error: This version of the software was released ' . 'after your download access expired. Please ' . 'downgrade or contact support for more information.', 'missing_license_key' => 'Error: The license key variable is empty.', 'unknown_local_key_type' => 'Error: An unknown type of local key validation was requested.', 'could_not_obtain_local_key' => 'Error: I could not obtain a new local license key.', 'maximum_grace_period_expired' => 'Error: The maximum local license key grace period has expired.', 'local_key_tampering' => 'Error: The local license key has been tampered with or is invalid.', 'local_key_invalid_for_location' => 'Error: The local license key is invalid for this location.', 'missing_license_file' => 'Error: Please create the following file (and directories if they don\'t exist already):<br />
<br />
', 'license_file_not_writable' => 'Error: Please make the following path writable:<br />', 'invalid_local_key_storage' => 'Error: I could not determine the local key storage on clear.', 'could_not_save_local_key' => 'Error: I could not save the local license key.', 'license_key_string_mismatch' => 'Error: The local key is invalid for this license.' );
		$this->localization = array( 'active' => 'This license is active.', 'suspended' => 'Error: This license has been suspended.', 'expired' => 'Error: This license has expired.', 'pending' => 'Error: This license is pending review.', 'download_access_expired' => 'Error: This version of the software was released ' . 'after your download access expired. Please ' . 'downgrade or contact support for more information.' );
	}

	/**
	 * Validate the license
	 *
	 * @return string
	 */
	function validate() {
		if (!$this->license_key) {
			return $this->errors = $this->status_messages['missing_license_key'];
		}


		if (!in_array( strtolower( $this->local_key_type ), $this->valid_local_key_types )) {
			return $this->errors = $this->status_messages['unknown_local_key_type'];
		}

		$this->trigger_grace_period = $this->status_messages['could_not_obtain_local_key'];
		switch ($this->local_key_storage) {
		case 'database': {
				$this->db_read_local_key(  );
				$local_key = ;
				break;
			}

		case 'filesystem': {
				$this->read_local_key(  );
				$local_key = ;
				break;
			}

		default: {
				$this->errors = $this->status_messages['missing_license_key'];
			}
		}

		return ;
	}

	/**
	 * Calculate the maximum grace period in unix timestamp.
	 *
	 * @param integer $local_key_expires
	 * @param integer $grace
	 * @return integer
	 */
	function calc_max_grace($local_key_expires, $grace) {
		return (int)$local_key_expires + (int)$grace * 86400;
	}

	/**
	 * Process the grace period for the local key.
	 *
	 * @param string $local_key
	 * @return string
	 */
	function process_grace_period($local_key) {
		$this->decode_key( $local_key );
		$local_key_src = ;
		$this->split_key( $local_key_src );
		$parts = ;
		unserialize( $parts[0] );
		$key_data = ;
		unset( $parts );
		unset( $key_data );
		$write_new_key = false;
		explode( '

', $local_key );
		$parts = ;
		$parts[0];
		$local_key = ;
		explode( ',', $this->local_key_grace_period );
		foreach ($local_key_grace_period = $local_key_expires = (int)$key_data['local_key_expires'] as ) {
			[0];
			[1];
			$grace = ;
			$key = ;

			if (!$key) {
				$local_key .= '
';
			}


			if (time(  ) < $this->calc_max_grace( $local_key_expires, $grace )) {
				continue;
			}

			$local_key .= ( '' . '
' ) . $grace;
			$write_new_key = true;
		}


		if ($this->calc_max_grace( $local_key_expires, array_pop( $local_key_grace_period ) ) < time(  )) {
			return array( 'write' => false, 'local_key' => '', 'errors' => $this->status_messages['maximum_grace_period_expired'] );
		}

		return array( 'write' => $write_new_key, 'local_key' => $local_key, 'errors' => false );
	}

	/**
	 * Are we still in a grace period?
	 *
	 * @param string $local_key
	 * @param integer $local_key_expires
	 * @return integer
	 */
	function in_grace_period($local_key, $local_key_expires) {
		$grace = $this->split_key( $local_key, '

' );

		if (!isset( $grace[1] )) {
			return 0 - 1;
		}

		return (int)$this->calc_max_grace( $local_key_expires, array_pop( explode( '
', $grace[1] ) ) ) - time(  );
	}

	/**
	 * Validate the local license key.
	 *
	 * @param string $local_key
	 * @return string
	 */
	function decode_key($local_key) {
		return base64_decode( str_replace( '
', '', urldecode( $local_key ) ) );
	}

	/**
	 * Validate the local license key.
	 *
	 * @param string $local_key
	 * @param string $token		{spbas} or \n\n
	 * @return string
	 */
	function split_key($local_key, $token = '{spbas}') {
		return explode( $token, $local_key );
	}

	/**
	 * Does the key match anything valid?
	 *
	 * @param string $key
	 * @param array $valid_accesses
	 * @return array
	 */
	function validate_access($key, $valid_accesses) {
		return in_array( $key, (array)$valid_accesses );
	}

	/**
	 * Create an array of wildcard IP addresses
	 *
	 * @param string $key
	 * @param array $valid_accesses
	 * @return array
	 */
	function wildcard_ip($key) {
		explode( '.', $key );
		$octets = ;
		array_pop( $octets );
		$ip_range[] = implode( '.', $octets ) . '.*';
		array_pop( $octets );
		$ip_range[] = implode( '.', $octets ) . '.*';
		array_pop( $octets );
		$ip_range[] = implode( '.', $octets ) . '.*';
		return $ip_range;
	}

	/**
	 * Create an array of wildcard IP addresses
	 *
	 * @param string $key
	 * @param array $valid_accesses
	 * @return array
	 */
	function wildcard_domain($key) {
		return '*.' . str_replace( 'www.', '', $key );
	}

	/**
	 * Create a wildcard server hostname
	 *
	 * @param string $key
	 * @param array $valid_accesses
	 * @return array
	 */
	function wildcard_server_hostname($key) {
		explode( '.', $key );
		$hostname = ;
		unset( $hostname[0] );
		$hostname = (!isset( $hostname[1] ) ? array( $key ) : $hostname);
		return '*.' . implode( '.', $hostname );
	}

	/**
	 * Extract a specific set of access details from the instance
	 *
	 * @param array $instances
	 * @param string $enforce
	 * @return array
	 */
	function extract_access_set($instances, $enforce) {
		foreach ($instances as ) {
			[0];
			[1];
			$instance = ;
			$key = ;

			if ($key != $enforce) {
				continue;
			}

			return $instance;
		}

		return array(  );
	}

	/**
	 * Validate the local license key.
	 *
	 * @param string $local_key
	 * @return string
	 */
	function validate_local_key($local_key) {
		$this->decode_key( $local_key );
		$local_key_src = ;
		$this->split_key( $local_key_src );
		$parts = ;

		if (!isset( $parts[1] )) {
			return $this->errors = $this->status_messages['local_key_tampering'];
		}


		if (md5( $this->secret_key . $parts[0] ) != $parts[1]) {
			return $this->errors = $this->status_messages['local_key_tampering'];
		}

		unset( [secret_key] );
		unserialize( $parts[0] );
		$key_data = ;
		$key_data['instance'];
		$instance = ;
		unset( $key_data[instance] );
		$key_data['enforce'];
		$enforce = ;
		unset( $key_data[enforce] );
		$this->key_data = $key_data;

		if ((bool)$key_data['license_key_string'] != (bool)$this->license_key) {
			return $this->errors = $this->status_messages['license_key_string_mismatch'];
		}


		if ((bool)$key_data['status'] != 'active') {
			return $this->errors = $this->status_messages[$key_data['status']];
		}


		if (( (bool)$key_data['license_expires'] != 'never' && (int)$key_data['license_expires'] < time(  ) )) {
			return $this->errors = $this->status_messages['expired'];
		}


		if (( (bool)$key_data['local_key_expires'] != 'never' && (int)$key_data['local_key_expires'] < time(  ) )) {
			if ($this->in_grace_period( $local_key, $key_data['local_key_expires'] ) < 0) {
				$this->clear_cache_local_key( true );
				return $this->validate(  );
			}
		}


		if (( ( $this->validate_download_access && strtolower( $key_data['download_access_expires'] ) != 'never' ) && (int)$key_data['download_access_expires'] < strtotime( $this->release_date ) )) {
			return $this->errors = $this->status_messages['download_access_expired'];
		}

		$conflicts = array(  );
		$this->access_details(  );
		$access_details = ;
		foreach ((array)$enforce as ) {
			[0];
			$key = ;
			$this->extract_access_set( $instance, $key );
			$valid_accesses = ;

			if (!$this->validate_access( $access_details[$key], $valid_accesses )) {
				$conflicts[$key] = true;

				if (in_array( $key, array( 'ip', 'server_ip' ) )) {
					foreach ($this->wildcard_ip( $access_details[$key] ) as ) {
						[0];
						$ip = ;

						if ($this->validate_access( $ip, $valid_accesses )) {
							unset( $conflicts[$key] );
							break;
						}
					}

					continue;
				}


				if (in_array( $key, array( 'domain' ) )) {
					if ($this->validate_access( $this->wildcard_domain( $access_details[$key] ), $valid_accesses )) {
						unset( $conflicts[$key] );
						continue;
					}

					continue;
				}


				if (in_array( $key, array( 'server_hostname' ) )) {
					if ($this->validate_access( $this->wildcard_server_hostname( $access_details[$key] ), $valid_accesses )) {
						unset( $conflicts[$key] );
						continue;
					}

					continue;
				}

				continue;
			}
		}


		if (!empty( $conflicts )) {
			return $this->errors = $this->status_messages['local_key_invalid_for_location'];
		}

	}

	/**
	 * Read in a new local key from the database.
	 *
	 * @return string
	 */
	function db_read_local_key() {
		@mysql_query( $this->read_query );
		$query = ;

		if ($mysql_error = ) {
			return $this->errors = '' . 'Error: ' . $mysql_error;
		}

		@mysql_fetch_assoc( $query );
		$result = mysql_error(  );
		mysql_error(  );

		if ($mysql_error = ) {
			return $this->errors = '' . 'Error: ' . $mysql_error;
		}


		if (!$result['local_key']) {
			$result['local_key'] = $this->fetch_new_local_key(  );

			if ($this->errors) {
				return $this->errors;
			}

			$this->db_write_local_key( $result['local_key'] );
		}

		return $this->local_key_last = $result['local_key'];
	}

	/**
	 * Write the local key to the database.
	 *
	 * @return string|boolean string on error; boolean true on success
	 */
	function db_write_local_key($local_key) {
		@mysql_query( @str_replace( '{local_key}', $local_key, $this->update_query ) );
		mysql_error(  );

		if ($mysql_error = ) {
			return $this->errors = '' . 'Error: ' . $mysql_error;
		}

		return true;
	}

	/**
	 * Read in the local license key.
	 *
	 * @return string
	 */
	function read_local_key() {
		if (!file_exists( $path = '' . $this->local_key_path . $this->local_key_name )) {
			return $this->errors = $this->status_messages['missing_license_file'] . $path;
		}


		if (!is_writable( $path )) {
			return $this->errors = $this->status_messages['license_file_not_writable'] . $path;
		}

		@file_get_contents( $path );

		if (!$local_key = ) {
			$this->fetch_new_local_key(  );
			$local_key = ;

			if ($this->errors) {
				return $this->errors;
			}

			$this->write_local_key( urldecode( $local_key ), $path );
		}

		return $this->local_key_last = $local_key;
	}

	/**
	 * Clear the local key file cache by passing in ?clear_local_key_cache=y
	 *
	 * @param boolean $clear
	 * @return string on error
	 */
	function clear_cache_local_key($clear = false) {
		switch (strtolower( $this->local_key_storage )) {
		case 'database': {
				$this->db_write_local_key( '' );
				break;
			}

		case 'filesystem': {
				$this->write_local_key( '', '' . $this->local_key_path . $this->local_key_name );
				break;
			}

		default: {
				$this->errors = $this->status_messages['invalid_local_key_storage'];
			}
		}

		return ;
	}

	/**
	 * Write the local key to a file for caching.
	 *
	 * @param string $local_key
	 * @param string $path
	 * @return string|boolean string on error; boolean true on success
	 */
	function write_local_key($local_key, $path) {
		@fopen( $path, 'w' );
		$fp = ;

		if (!$fp) {
			return $this->errors = $this->status_messages['could_not_save_local_key'];
		}

		@fwrite( $fp, $local_key );
		@fclose( $fp );
		return true;
	}

	/**
	 * Query the API for a new local key
	 *
	 * @return string|false string local key on success; boolean false on failure.
	 */
	function fetch_new_local_key() {
		$querystring = '' . 'mod=license&task=SPBAS_validate_license&license_key=' . $this->license_key . '&';
		$this->build_querystring( $this->access_details(  ) );
		$querystring .= ;

		if ($this->errors) {
			return false;
		}

		$this->local_key_transport_order;
		$priority = ;

		while (strlen( $priority )) {
			substr( $priority, 0, 1 );
			$use = ;

			if ($use == 's') {
				$this->use_fsockopen( $this->api_server, $querystring );

				if ($result = ) {
					break;
				}
			}


			if ($use == 'c') {
				$this->use_curl( $this->api_server, $querystring );

				if ($result = ) {
					break;
				}
			}


			if ($use == 'f') {
				$this->use_fopen( $this->api_server, $querystring );

				if ($result = ) {
					break;
				}
			}

			substr( $priority, 1 );
			$priority = ;
		}


		if (!$result) {
			$this->errors = $this->status_messages['could_not_obtain_local_key'];
			return false;
		}


		if (substr( $result, 0, 7 ) == 'Invalid') {
			$this->errors = str_replace( 'Invalid', 'Error', $result );
			return false;
		}


		if (substr( $result, 0, 5 ) == 'Error') {
			$this->errors = $result;
			return false;
		}

		return $result;
	}

	/**
	 * Convert an array to querystring key/value pairs
	 *
	 * @param array $array
	 * @return string
	 */
	function build_querystring($array) {
		$buffer = '';
		foreach ((array)$array as ) {
			[0];
			[1];
			$value = ;
			$key = ;

			if ($buffer) {
				$buffer .= '&';
			}

			$buffer .= '' . $key . '=' . $value;
		}

		return $buffer;
	}

	/**
	 * Build an array of access details
	 *
	 * @return array
	 */
	function access_details() {
		$access_details = array(  );

		if (function_exists( 'phpinfo' )) {
			ob_start(  );
			phpinfo(  );
			ob_get_contents(  );
			$phpinfo = ;
			ob_end_clean(  );
			strip_tags( $phpinfo );
			$list = ;
			$access_details['domain'] = $this->scrape_phpinfo( $list, 'HTTP_HOST' );
			$access_details['ip'] = $this->scrape_phpinfo( $list, 'SERVER_ADDR' );
			$access_details['directory'] = $this->scrape_phpinfo( $list, 'SCRIPT_FILENAME' );
			$access_details['server_hostname'] = $this->scrape_phpinfo( $list, 'System' );
			$access_details['server_ip'] = @gethostbyname( $access_details['server_hostname'] );
		}

		$access_details['domain'] = ($access_details['domain'] ? $access_details['domain'] : $_SERVER['HTTP_HOST']);
		$access_details['ip'] = ($access_details['ip'] ? $access_details['ip'] : $this->server_addr(  ));
		$access_details['directory'] = ($access_details['directory'] ? $access_details['directory'] : $this->path_translated(  ));
		$access_details['server_hostname'] = ($access_details['server_hostname'] ? $access_details['server_hostname'] : @gethostbyaddr( $access_details['ip'] ));
		$access_details['server_hostname'] = ($access_details['server_hostname'] ? $access_details['server_hostname'] : 'Unknown');
		$access_details['server_ip'] = ($access_details['server_ip'] ? $access_details['server_ip'] : @gethostbyaddr( $access_details['ip'] ));
		$access_details['server_ip'] = ($access_details['server_ip'] ? $access_details['server_ip'] : 'Unknown');
		foreach ($access_details as ) {
			[0];
			[1];
			$value = ;
			$key = ;
			$access_details[$key] = ($access_details[$key] ? $access_details[$key] : 'Unknown');
		}


		if ($this->valid_for_product_tiers) {
			$access_details['valid_for_product_tiers'] = $this->valid_for_product_tiers;
		}

		return $access_details;
	}

	/**
	 * Get the directory path
	 *
	 * @return string|boolean string on success; boolean on failure
	 */
	function path_translated() {
		$option = array( 'PATH_TRANSLATED', 'ORIG_PATH_TRANSLATED', 'SCRIPT_FILENAME', 'DOCUMENT_ROOT', 'APPL_PHYSICAL_PATH' );
		foreach ($option as ) {
			[0];
			$key = ;

			if (( !isset( $_SERVER[$key] ) || strlen( trim( $_SERVER[$key] ) ) <= 0 )) {
				continue;
			}


			if (( $this->is_windows(  ) && strpos( $_SERVER[$key], '\' ) )) {
					return substr( $_SERVER[$key], 0, @strrpos( $_SERVER[$key], '\' ) );
				}

				return substr( $_SERVER[$key], 0, @strrpos( $_SERVER[$key], '/' ) );
			}

			return false;
		}

		/**
	* Get the server IP address
	*
	* @return string|boolean string on success; boolean on failure
	*/
		function server_addr() {
			$options = array( 'SERVER_ADDR', 'LOCAL_ADDR' );
			foreach ($options as ) {
				[0];
				$key = ;

				if (isset( $_SERVER[$key] )) {
					return $_SERVER[$key];
				}
			}

			return false;
		}

		/**
	* Get access details from phpinfo()
	*
	* @param array $all
	* @param string $target
	* @return string|boolean string on success; boolean on failure
	*/
		function scrape_phpinfo($all, $target) {
			$all = explode( $target, $all );

			if (count( $all ) < 2) {
				return false;
			}

			$all = explode( '
						', $all[1] );
			$all = trim( $all[0] );

			if ($target == 'System') {
				$all = explode( ' ', $all );
				$all = trim( $all[(( strtolower( $all[0] ) == 'windows' && strtolower( $all[1] ) == 'nt' ) ? 2 : 1)] );
			}


			if ($target == 'SCRIPT_FILENAME') {
				$slash = ($this->is_windows(  ) ? '\' : '/');
				$all = explode( $slash, $all );
				array_pop( $all );
				$all = implode( $slash, $all );
			}


			if (substr( $all, 1, 1 ) == ']') {
				return false;
			}

			return $all;
		}

		/**
	* Pass the access details in using fsockopen
	*
	* @param string $url
	* @param string $querystring
	* @return string|boolean string on success; boolean on failure
	*/
		function use_fsockopen($url, $querystring) {
			if (!function_exists( 'fsockopen' )) {
				return false;
			}

			parse_url( $url );
			$url = ;
			@fsockopen( $url['host'], $this->remote_port, $errno, $errstr, $this->remote_timeout );
			$fp = ;

			if (!$fp) {
				return false;
			}

			$header = '' . 'POST ' . $url['path'] . ' HTTP/1.0
						';
			$header .= ( ( '' . 'Host: ' . $url['host'] . '
						' ) . '
						' );
			$header .= 'Content-type: application/x-www-form-urlencoded
						';
			$header .= 'User-Agent: SPBAS (http://www.spbas.com)
							';
			$header .= 'Content-length: ' . @strlen( $querystring ) . '
							';
			$header .= 'Connection: close

							';
			$header .= ;
			$result = false;
			fputs( $fp, $header );

			while (!feof( $fp )) {
				fgets( $fp, 1024 );
				$result .= ;
			}

			fclose( $fp );

			if (strpos( $result, '200' ) === false) {
				return false;
			}

			explode( '

							', $result, 2 );
			$result = $querystring;

			if (!$result[1]) {
				return false;
			}

			return $result[1];
		}

		/**
	* Pass the access details in using cURL
	*
	* @param string $url
	* @param string $querystring
	* @return string|boolean string on success; boolean on failure
	*/
		function use_curl($url, $querystring) {
			if (!function_exists( 'curl_init' )) {
				return false;
			}

			curl_init(  );
			$curl = ;
			$header[0] = 'Accept: text/xml,application/xml,application/xhtml+xml,';
			$header->178 .= 'text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
			$header[] = 'Cache-Control: max-age=0';
			$header[] = 'Connection: keep-alive';
			$header[] = 'Keep-Alive: 300';
			$header[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
			$header[] = 'Accept-Language: en-us,en;q=0.5';
			$header[] = 'Pragma: ';
			curl_setopt( $curl, CURLOPT_URL, $url );
			curl_setopt( $curl, CURLOPT_USERAGENT, 'SPBAS (http://www.spbas.com)' );
			curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
			curl_setopt( $curl, CURLOPT_ENCODING, 'gzip,deflate' );
			curl_setopt( $curl, CURLOPT_AUTOREFERER, true );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $querystring );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->remote_timeout );
			curl_setopt( $curl, CURLOPT_TIMEOUT, $this->remote_timeout );
			curl_exec( $curl );
			$result = ;
			curl_getinfo( $curl );
			$info = ;
			curl_close( $curl );

			if ((int)$info['http_code'] != 200) {
				return false;
			}

			return $result;
		}

		/**
	* Pass the access details in using the fopen wrapper file_get_contents()
	*
	* @param string $url
	* @param string $querystring
	* @return string|boolean string on success; boolean on failure
	*/
							function use_fopen($url, $querystring) {
								if (!function_exists( 'file_get_contents' )) {
									return false;
								}

								return @file_get_contents( '' . $url . '?' . $querystring );
							}

							/**
							 * Determine if we are running windows or not.
							 *
							 * @return boolean
							 */
							function is_windows() {
								return strtolower( substr( php_uname(  ), 0, 7 ) ) == 'windows';
							}

							/**
							 * Debug - prints a formatted array
							 *
							 * @param array $stack The array to display
							 * @param boolean $stop_execution
							 * @return string
							 */
							function pr($stack, $stop_execution = true) {
								$formatted = '<pre>' . var_export( (array)$stack, 1 ) . '</pre>';

								if ($stop_execution) {
									exit( $formatted );
								}

								return $formatted;
							}
						}


						if (dirname( $_SERVER['SCRIPT_NAME'] ) == '/') {
							$trailing = '';
						}
						else {
							$trailing = '/';
						}

						define( 'HOST', $_SERVER['HTTP_HOST'] );
						define( 'BASE_PATH', dirname( __FILE__ ) . '/' );
						define( 'WEBSITE_URL', 'http://' . HOST . dirname( $_SERVER['SCRIPT_NAME'] ) . $trailing );
						define( 'WEBSITE_ROOT', dirname( $_SERVER['SCRIPT_NAME'] ) . $trailing );
						define( 'DIR_SEP', '/' );
						define( 'IS_IN_SCRIPT', 1 );
						ini_set( 'zlib.output_compression', 'On' );
						error_reporting( 0 );
						session_start(  );
						require_once( BASE_PATH . 'includes/constants.php' );
						require_once( BASE_PATH . 'includes/dbconfig.php' );
						require_once( BASE_PATH . 'includes/helpers.php' );

						if (file_exists( 'install' )) {
							$install_url = WEBSITE_URL . 'install/';

							if (!INSTALL_COMPLETE) {
								header( '' . 'Location: ' . $install_url );
								exit(  );
							}
						}


						if (basename( $_SERVER['SCRIPT_FILENAME'] ) == 'core.php') {
							exit( 'Cannot access this file directly' );
						}

						newClass( 'Dbase' );
						$dbase = ;
						newClass( 'Form' );
						$form = ;
						$dbase->defineSettings(  );
						newClass( 'Functions' );
						$func = ;

						if (SEO_ENABLED) {
							$func->assignQueryValues(  );
						}

						newClass( 'Mailer' );
						$mailer = ;
						newClass( 'Session' );
						$session = ;
						newClass( 'Process' );
						$process = ;
						newClass( 'SimpleImage' );
						$image = ;
						spbas;
						new (  );
						$spbas = ;
						$spbas->license_key = (( defined( 'LICENSE_KEY' ) && LICENSE_KEY != '' ) ? LICENSE_KEY : '');
						$spbas->local_key_path = BASE_PATH;
						$spbas->local_key_name = 'license.txt';
						$spbas->api_server = 'http://spicyscripts.com/spbas/api/index.php';
						$spbas->secret_key = '72c09d6a411ed5a1bc6fa869b02d6506';
						$spbas->validate(  );

						if ($spbas->errors) {
							if (( basename( $_SERVER['SCRIPT_NAME'] ) == 'admin.php' && ADMIN_MODE )) {
								warnLicenseError( $spbas->errors );
							}
							else {
								exit( $spbas->errors );
							}
						}

						unset( $spbas );
						$queystring = (isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '');
						$page_url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . substr( $session->url, 1 );
						$cache_enabled = false;
						$cachefile = BASE_PATH . 'cache/pages/' . md5( $page_url ) . '.html';
						$cachetime = CACHETIME * 86400;

						if (( ( $cache_enabled && file_exists( $cachefile ) ) && time(  ) - $cachetime < filemtime( $cachefile ) )) {
							include( $cachefile );
							echo '<!-- Cached ' . date( 'H:i', filemtime( $cachefile ) ) . ' -->';
							exit(  );
						}

						ob_start(  );
						include_once( BASE_PATH . 'includes/smarty/Smarty.class.php' );
						include_once( BASE_PATH . 'includes/restricted_locations.php' );
						include_once( BASE_PATH . 'includes/template.php' );
						require_once( BASE_PATH . 'includes/recaptchalib.php' );
						include_once( BASE_PATH . 'includes/languages/' . $session->endPath . $session->language . '.php' );

						if (isset( $_GET['logout'] )) {
							exit(  );
						}

						$location = (isset( $_GET['l'] ) ? $_GET['l'] : '');
						explode( '.', $location );
						$locData = ;
						$locData[0];
						$module = ;
						$tpl_base = WEBSITE_ROOT . 'templates/' . $session->endPath . ($session->template ? $session->template . '/' : '');
						$theme_base = $tpl_base . 'themes/' . ($session->theme ? $session->theme . '/' : '');
						$js_base = WEBSITE_ROOT . 'js/' . $session->endPath;
						$js_vars = '<script type="text/javascript">var CPAGE = "' . $location . '"; var USERNAME = "' . $session->username . '"; var CURRENT_TEMPLATE = "' . $session->template . '"; var website_url = "' . WEBSITE_ROOT . $session->adminUrl . '"; var theme_base = "' . $theme_base . '"; var tpl_base = "' . $tpl_base . '"; var js_base = "' . $js_base . '"; var seo_enabled = ' . (SEO_ENABLED ? 'true' : 'false') . ';</script>';
						Template;
						new (  );
						$tpl = ;
						include( 'includes/assign_global_vars.php' );
						$breadcrumbs = array(  );

						if ($location != '') {
							$ajax = (( $_GET['ajax'] == 'on' || $_POST['ajax'] == 'on' ) ? true : false);
							$lite = (( $_GET['lite'] == 'on' || $_POST['lite'] == 'on' ) ? true : false);
							$_GET['z'];
							$zone = ;

							if (!$session->isRestricted( $location )) {
								if (!$ajax) {
									$module_path = 'pages/' . $session->endPath . '' . $location . '.php';
									$js_path = 'pages/' . $session->endPath . 'js/' . $location . '.js';
									$tpl_path = $location . '.html';
								}
								else {
									$module_path = 'pages/' . $session->endPath . 'ajax/' . $location . '.php';
									$tpl_path = 'ajax/' . $location . '.html';
								}
							}
							else {
								if (!$ajax) {
									$tpl->assign( 'refererLoc', $location );
									$tpl->assign( 'refererParams', $func->implodeGet(  ) );
									$location = 'login';
									$module_path = 'pages/' . $session->endPath . '' . $location . '.php';
									$js_path = 'pages/' . $session->endPath . 'js/' . $location . '.js';
									$tpl_path = $location . '.html';
								}
								else {
									echo 'restricted';
									exit(  );
								}
							}
						}
						else {
							if (!$session->isRestricted( $location )) {
								$location = 'home';
								$module_path = 'pages/' . $session->endPath . '' . $location . '.php';
								$js_path = 'pages/' . $session->endPath . 'js/' . $location . '.js';
								$tpl_path = $location . '.html';
							}
							else {
								$tpl->assign( 'refererLoc', $location );
								$tpl->assign( 'refererParams', $func->implodeGet(  ) );
								$location = 'login';
								$module_path = 'pages/' . $session->endPath . '' . $location . '.php';
								$js_path = 'pages/' . $session->endPath . 'js/' . $location . '.js';
								$tpl_path = $location . '.html';
							}
						}

						$checkTpl = BASE_PATH . 'templates/' . $session->endPath . $session->template . '/' . $tpl_path;
						$checkMod = BASE_PATH . $module_path;

						if (( is_file( $checkTpl ) || is_file( $checkMod ) )) {
							clearstatcache( true, $checkTpl );
							clearstatcache( true, $checkMod );
							include( BASE_PATH . $module_path );
						}
						else {
							if (!$ajax) {
								$location = 'error';
								$module_path = 'pages/' . $session->endPath . '' . $location . '.php';
								$js_path = 'pages/' . $session->endPath . 'js/' . $location . '.js';
								$tpl_path = $location . '.html';
								include( BASE_PATH . $module_path );
							}
							else {
								echo 'Ajax Page Not Found!';
								exit(  );
							}
						}

						$checkJS = BASE_PATH . $js_path;

						if ($page_title == '') {
							ucFirst( str_replace( '.', ' ', $location ) );
							$page_title = ;
							$extraTitle = '';

							if (SEO_ENABLED) {
								$i = 10;

								while ($i < sizeof( $_GET['trailingParam'] )) {
									if (( ( $_GET['trailingParam'][$i] != '?' && $_GET['trailingParam'][$i] != 'index.php' ) && $_GET['trailingParam'][$i] != 'admin.php' )) {
										str_replace( '_', ' ', $_GET['trailingParam'][$i] );
										$extraTitle .= ;
									}

									++$i;
								}
							}
							else {
								if (isset( $_GET['t'] )) {
									str_replace( '_', ' ', $_GET['t'] );
									$extraTitle = ;
								}
							}

							$extraTitle;
							$page_title = ;
						}


						if (( !$ajax && !$lite )) {
							if ($location != 'home') {
								$page_description = ($page_description == '' ? $page_title : $page_description);
								$page_keywords = ($page_keywords == '' ? $page_title : $page_keywords);
								$page_image = ($page_image != '' ? $page_image : '');

								if (( empty( $breadcrumbs ) && $location != 'error' )) {
									str_replace( '.', ' ', $location );
									$breadcrumb = ;
									$breadcrumbs[] = array( 'title' => $func->lang( ucfirst( $breadcrumb ) ), 'link' => $func->link( $location ) );
								}
							}


							if (( isset( $js_path ) && is_file( $checkJS ) )) {
								$tpl->assign( 'module_js', $js_path );
							}

							$page_title = ($page_title != '' ? $page_title : $func->lang( WEBSITE_TITLE ) . ' - ' . WEBSITE_NAME);
							$page_description = ($page_description != '' ? $page_description : $func->lang( WEBSITE_DESCRIPTION ));
							$page_keywords = ($page_keywords != '' ? $page_keywords : WEBSITE_KEYWORDS);
							$page_image = ($page_image != '' ? $page_image : FB_DEFAULT_OG_IMAGE);
							$tpl->assign( 'page_title', $page_title );
							$tpl->assign( 'page_description', $page_description );
							$tpl->assign( 'page_keywords', $page_keywords );
							$tpl->assign( 'page_image', $page_image );
							$tpl->assign( 'page_url', $page_url );
							$tpl->assign( 'breadcrumbs', $breadcrumbs );
							$tpl->assign( 'location', $location );
							$tpl->assign( 'module', $module );
							$tpl->assign( 'querystring', $querystring );

							if (isset( $TPL_HEADER )) {
								$TPL_HEADER;
								$header = ;
							}
							else {
								$header = 'header.html';
							}

							$tpl->display( $header );
						}

						$tpl->display( $tpl_path );

						if (( !$ajax && !$lite )) {
							if (isset( $TPL_FOOTER )) {
								$TPL_FOOTER;
								$footer = ;
							}
							else {
								$footer = 'footer.html';
							}

							$tpl->display( $footer );
						}

						$dbase->close(  );

						if ($cache_enabled) {
							fopen( $cachefile, 'w' );
							$fp = ;
							fwrite( $fp, ob_get_contents(  ) );
							fclose( $fp );
						}

						ob_end_flush(  );
?>
