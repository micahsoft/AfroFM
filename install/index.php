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
		$this->local_key_transport_order = 'scf';
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
		$local_key_expires = (int)$key_data['local_key_expires'];
		unset( $parts );
		unset( $key_data );
		$write_new_key = false;
		explode( '

', $local_key );
		$parts = ;
		$parts[0];
		$local_key = ;
		explode( ',', $this->local_key_grace_period );
		foreach ($local_key_grace_period =  as ) {
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
		$this->split_key( $local_key, '

' );
		$grace = ;

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
		mysql_error(  );

		if ($mysql_error = ) {
			return $this->errors = '' . 'Error: ' . $mysql_error;
		}

		@mysql_fetch_assoc( $query );
		$result = ;
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
			$priority = $use = ;
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
			explode( $target, $all );
			$all = ;

			if (count( $all ) < 2) {
				return false;
			}

			explode( '
						', $all[1] );
			$all = ;
			trim( $all[0] );
			$all = ;

			if ($target == 'System') {
				explode( ' ', $all );
				$all = ;
				trim( $all[(( strtolower( $all[0] ) == 'windows' && strtolower( $all[1] ) == 'nt' ) ? 2 : 1)] );
				$all = ;
			}


			if ($target == 'SCRIPT_FILENAME') {
				$slash = ($this->is_windows(  ) ? '\' : '/');
				explode( $slash, $all );
				$all = ;
				array_pop( $all );
				implode( $slash, $all );
				$all = ;
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
			$querystring;
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
			$result = ;

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
			$result = curl_setopt( $curl, CURLOPT_URL, $url );
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

						function setHeader() {
							$header = '
		<html>
		<head>
			<title>' . APP_NAME . ' v' . APP_VERSION . ' Installer</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
		</head>
		<body>
			<div class="wrapper">
			<div class="logobg"><span>Installer</span></div>
			<h1 id="logo"><img alt="' . APP_NAME . '" src="images/install-header.png" /><span>' . APP_NAME . ' v' . APP_VERSION . '</span></h1>
			<div class="steps">' . getSteps(  ) . '</div>
	';
							echo $header;
						}

						function setFooter() {
							$footer = '
		</div>
		</body>
		</html>
	';
							echo $footer;
						}

						function getCurrentStep() {
							$currentStep = (isset( $_GET['step'] ) ? $_GET['step'] : 1);

							if (( $currentStep != 1 && !isset( $_SESSION['step_' . ( $currentStep - 1 )] ) )) {
								$currentStep = 10;
							}

							return $currentStep;
						}

						function getSteps() {
							global $settings;

							getCurrentStep(  );
							$currentStep = ;
							$output = '';
							$i = 10;

							while ($i < sizeof( $settings['steps'] )) {
								$class = '';

								if ($currentStep < $i + 1) {
									$output .= '<span class="' . $class . '">' . _lang( 'Step', true ) . ' ' . ( $i + 1 ) . ' - ' . $settings['steps'][$i] . '</span>';
								}
								else {
									$class = 'active';
									$output .= '<a href="?step=' . ( $i + 1 ) . '"><span class="' . $class . '">' . _lang( 'Step', true ) . ' ' . ( $i + 1 ) . ' - ' . $settings['steps'][$i] . '</span></a>';
								}

								++$i;
							}

							return $output;
						}

						function checkPermissions($silent = false) {
							global $settings;

							if (!$silent) {
								_echo( 'Checking Permissions...', 'progress' );
							}

							$success = true;
							foreach ($settings['permissions'] as ) {
								[0];
								$dir = ;

								if (( !file_exists( $dir ) && substr( $dir, 0 - 3 ) != 'php' )) {
									if (!$silent) {
										_echo( substr( $dir, 3 ) . ' does not exist', 'error' );
									}

									$success = false;
									continue;
								}


								if (!is_writable( $dir )) {
									if (!$silent) {
										_echo( 'Unable to write to ' . substr( $dir, 3 ), 'progress error' );
									}

									$success = false;
									continue;
								}
							}


							if (( !$success && !$silent )) {
								_echo( 'Common cause of this is incorrect filesystem permissions.<br />Please chmod the above directories/files to 777 before continuing', 'error box' );
							}

							return $success;
						}

						function mysqlImport($filename, $data, $title) {
							$data['DB'];
							$dbInfo = ;
							$data['ADMIN'];
							$adminInfo = ;
							$data['SITE'];
							$siteInfo = ;
							$data['LICENSE'];
							$licenseInfo = ;
							$success = false;
							newClass( 'Dbase', $dbInfo );
							$dbase = ;

							if (!$dbase->db) {
								_echo( 'Cannot connect to database using these info!', 'box error' );
								return false;
							}

							startProcess( $title );
							startTerminal(  );
							$sql_start = array( 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'GRANT', 'REVOKE', 'CREATE', 'ALTER' );
							$sql_run_last = array( 'INSERT' );

							if (file_exists( $filename )) {
								file( $filename );
								$lines = ;
								$queries = array(  );
								$query = '';

								if (is_array( $lines )) {
									foreach ($lines as ) {
										[0];
										$line = ;
										trim( $line );
										$line = ;
										preg_replace( array( '/\{\{admin_user\}\}/', '/\{\{admin_pass\}\}/', '/\{\{admin_email\}\}/', '/\{\{site_name\}\}/', '/\{\{site_description\}\}/', '/\{\{site_keywords\}\}/', '/\{\{license_key\}\}/' ), array( $adminInfo['ADMIN_USER'], $adminInfo['ADMIN_PASS'], $adminInfo['ADMIN_EMAIL'], $siteInfo['SITE_NAME'], $siteInfo['SITE_DESCRIPTION'], $siteInfo['SITE_KEYWORDS'], $licenseInfo['LICENSE_KEY'] ), $line );
										$line = ;
										echo $line . '<br>';

										if (!preg_match( '\'^--\'', $line )) {
											if (!trim( $line )) {
												if ($query != '') {
													trim( strtoupper( substr( $query, 0, strpos( $query, ' ' ) ) ) );
													$first_word = ;

													if (in_array( $first_word, $sql_start )) {
														$pos = strpos( $query, '`' ) + 1;
														$query = substr( $query, 0, $pos ) . substr( $query, $pos );
													}

													$priority = 13;

													if (in_array( $first_word, $sql_run_last )) {
														$priority = 22;
													}

													$queries[$priority][] = $query;
													$query = '';
													continue;
												}

												continue;
											}

											$line;
											$query .= ;
											continue;
										}
									}

									ksort( $queries );
									foreach ($queries as ) {
										[0];
										[1];
										$to_run = ;
										$priority = ;
										foreach ($to_run as ) {
											[0];
											[1];
											$sql = ;
											$i = ;
											$dbase->exec( $sql );
										}
									}

									$success = true;
								}
							}

							endTerminal(  );

							if ($success) {
								endProcess( '100% Complete', 'progress success nomargin' );
							}
							else {
								endProcess( 'Failed Importing mySQL DATA', 'progress error nomargin' );
							}

							_echo( '<br>' );
							$dbase->close(  );
							return $success;
						}

						function writeDbConfig($dbconfig) {
							$content = '<?php
/**
 * Database Constants - these constants are required
 * in order for there to be a successful connection
 * to the MySQL database. Make sure the information is
 * correct.
 */

define("DB_HOST", "' . $dbconfig['DB_HOST'] . '");
define("DB_USER", "' . $dbconfig['DB_USER'] . '");
define("DB_PASS", "' . $dbconfig['DB_PASS'] . '");
define("DB_NAME", "' . $dbconfig['DB_NAME'] . '");
define("INSTALL_COMPLETE", true);
?>';
							$dbConfigFile = '../includes/dbconfig.php';
							fopen( $dbConfigFile, 'w' );

							if (!( $fh = )) {
								exit( 'Cannot open file: ' . $dbConfigFile );
								(bool)true;
							}

							fwrite( $fh, $content );
							fclose( $fh );
							return true;
						}

						function deleteDir($dirPath) {
							if (!is_dir( $dirPath )) {
								return false;
							}


							if (substr( $dirPath, strlen( $dirPath ) - 1, 1 ) != '/') {
								$dirPath .= '/';
							}

							glob( $dirPath . '*', GLOB_MARK );
							$files = ;
							foreach ($files as ) {
								[0];
								$file = ;

								if (is_dir( $file )) {
									deleteDir( $file );
									continue;
								}

								unlink( $file );
							}

							rmdir( $dirPath );
						}

						function startProcess($msg, $class = 'progress') {
							echo '<div class="' . $class . '">' . _lang( $msg, true );
						}

						function endProcess($msg, $class = 'success') {
							echo '<span class="' . $class . '">' . _lang( $msg, true ) . '</span></div>';
						}

						function startTerminal() {
							echo '<div class="terminal">';
							echo '<pre>';
						}

						function endTerminal() {
							echo '</pre>';
							echo '</div>';
						}

						function _echo($msg, $class = 'progress', $return = false) {
							$text = '<div class="' . $class . '">' . _lang( $msg, true ) . '</div>';

							if ($return) {
								return $text;
							}

							echo $text;
						}

						function _lang($text, $return = false) {
							if ($return) {
								return $text;
							}

							echo $text;
						}

						function checkLicense($license) {
							spbas;
							new (  );
							$spbas = ;
							$spbas->license_key = ($license != '' ? $license : '');
							$spbas->local_key_path = '../';
							$spbas->local_key_name = 'license.txt';
							$spbas->api_server = 'http://spicyscripts.com/spbas/api/index.php';
							$spbas->secret_key = '72c09d6a411ed5a1bc6fa869b02d6506';
							$spbas->validate(  );

							if ($spbas->errors) {
								_echo( $spbas->errors, 'box error' );
								return false;
							}

							unset( $spbas );
							return true;
						}

						function loadStep($step = 1) {
							switch ($step) {
							case 1: {
									checkPermissions(  );
									$success = ;

									if ($success) {
										$output = _echo( 'File Permissions passed requirements...', 'box success', true ) . '
				<form method="post" action="?step=2">
					<p class="step"><input name="next" type="submit" value="Next" class="button" /></p>
				</form>';
										$_SESSION['step_1'] = 'done';
									}
									else {
										$output = '
				<form method="post" action="?step=1">
					<p class="step"><input name="submit" type="submit" value="Retry" class="button" /></p>
				</form>';
									}

									echo $output;
									break;
								}

							case 2: {
									if (!checkPermissions( true )) {
										unset( $_SESSION[step_1] );
										echo '<meta http-equiv="refresh" content="0; url=?step=1">';
										exit(  );
									}

									$success = false;

									if (isset( $_POST['submit'] )) {
										if (( ( ( ( ( ( ( ( !empty( $_POST['dbname'] ) && !empty( $_POST['uname'] ) ) && !empty( $_POST['pwd'] ) ) && !empty( $_POST['dbhost'] ) ) && !empty( $_POST['admin_user'] ) ) && !empty( $_POST['admin_pass'] ) ) && !empty( $_POST['admin_email'] ) ) && !empty( $_POST['site_name'] ) ) && !empty( $_POST['license_key'] ) )) {
											checkLicense( $_POST['license_key'] );
											$success = ;

											if ($success) {
												$DATA['DB'] = array( 'DB_HOST' => $_POST['dbhost'], 'DB_USER' => $_POST['uname'], 'DB_PASS' => $_POST['pwd'], 'DB_NAME' => $_POST['dbname'] );
												$DATA['ADMIN'] = array( 'ADMIN_USER' => $_POST['admin_user'], 'ADMIN_PASS' => md5( $_POST['admin_pass'] ), 'ADMIN_EMAIL' => $_POST['admin_email'] );
												$DATA['SITE'] = array( 'SITE_NAME' => $_POST['site_name'], 'SITE_DESCRIPTION' => $_POST['site_description'], 'SITE_KEYWORDS' => $_POST['site_keywords'] );
												$DATA['LICENSE'] = array( 'LICENSE_KEY' => $_POST['license_key'] );
												mysqlImport( 'sql/new_install.sql', $DATA, 'Creating MySQL Tables...' );
												$success = ;

												if ($success) {
													mysqlImport( 'sql/default_data.sql', $DATA, 'Importing Default Data...' );
													$success = ;
												}


												if ($success) {
													writeDbConfig( $DATA['DB'] );
													$success = ;
												}
											}
										}
										else {
											_echo( 'Please Fill All Fields', 'box error' );
										}
									}


									if (!$success) {
										$output = '
				<form method="post" action="?step=2">
					<div class="tip box">
						Please enter your database information. If you\'re not sure, contact your host.<br />
						Make sure the database is already exists before you proceed...
					</div>
					<table class="form-table">
						<tr>
							<td colspan="2"><h2>Database Info</h2></td>
						</tr>
						<tr>
							<th scope="row"><label for="dbname">Database Name <span>*</span></label></th>
							<td><input name="dbname" id="dbname" type="text" size="25" value="' . $_POST['dbname'] . '" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="uname">User Name <span>*</span></label></th>
							<td><input name="uname" id="uname" type="text" size="25" value="' . $_POST['uname'] . '" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="pwd">Password <span>*</span></label></th>
							<td><input name="pwd" id="pwd" type="password" size="25" value="' . $_POST['pwd'] . '" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="dbhost">Database Host <span>*</span></label></th>
							<td><input name="dbhost" id="dbhost" type="text" size="25" value="' . (isset( $_POST['dbhost'] ) ? $_POST['dbhost'] : 'localhost') . '" /></td>
						</tr>
						<tr>
							<td colspan="3"><h2>Site Info</h2></td>
						</tr>
						<tr>
							<th scope="row"><label for="site_name">Site Name <span>*</span></label></th>
							<td><input name="site_name" id="site_name" type="text" size="25" value="' . $_POST['site_name'] . '" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="site_description">Site Description</label></th>
							<td><textarea name="site_description" id="site_description">' . $_POST['site_description'] . '</textarea></td>
						</tr>
						<tr>
							<th scope="row"><label for="site_root">Site Keywords</label></th>
							<td><input name="site_keywords" id="site_keywords" type="text" size="25" value="' . $_POST['site_keywords'] . '" /></td>
						</tr>
						<tr>
							<td colspan="3"><h2>Admin Info</h2></td>
						</tr>
						<tr>
							<th scope="row"><label for="admin_user">Admin Username <span>*</span></label></th>
							<td><input name="admin_user" id="admin_user" type="text" size="25" value="' . (isset( $_POST['admin_user'] ) ? $_POST['admin_user'] : 'admin') . '" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="admin_pass">Admin Password <span>*</span></label></th>
							<td><input name="admin_pass" id="admin_pass" type="password" size="25" value="' . $_POST['admin_pass'] . '" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="admin_email">Admin Email <span>*</span></label></th>
							<td><input name="admin_email" id="admin_email" type="text" size="25" value="' . $_POST['admin_email'] . '" /></td>
						</tr>
						<tr>
							<td colspan="3"><h2>License Key</h2></td>
						</tr>
						<tr>
							<th scope="row"><label for="license_key">License Key <span>*</span></label></th>
							<td><input name="license_key" id="license_key" type="text" size="25" value="' . $_POST['license_key'] . '" /></td>
						</tr>
					</table>
					<p class="step"><input name="submit" type="submit" value="Next" class="button" /></p>
				</form>';
									}
									else {
										$output = '
				<form method="post" action="?step=3">
					<p class="step"><input name="submit" type="submit" value="Next" class="button" /></p>
				</form>';
										$_SESSION['step_2'] = 'done';
									}

									echo $output;
									break;
								}

							case 3: {
									$admin_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname( dirname( $_SERVER['SCRIPT_NAME'] ) ) . '/admin/';
									$output = _echo( '<h2>Installation is complete</h2><font color="red">Please delete or rename the /install folder before proceeding...</font>', 'box success', true ) . '
				<form method="post" action="' . $admin_url . '">
					<p class="step"><input name="next" type="submit" value="Admin Panel" class="button" /></p>
				</form>';
									echo $output;
								}
							}

						}

						function newClass($className) {
							func_num_args(  );
							$numargs = ;

							if (1 < $numargs) {
								func_get_args(  );
								$args = ;
								$args[1];
								$args = ;
							}
							else {
								$args = null;
							}

							require_once( '../classes/' . $className . '.class.php' );
							$className;
							new ( $args );
							$class = ;

							if ($args) {
								$className;
								new ( $args );
								$class = ;
							}
							else {
								$className;
								new (  );
								$class = ;
							}

							return $class;
						}

						function main() {
							setHeader(  );
							getCurrentStep(  );
							$step = ;
							loadStep( $step );
							setFooter(  );
						}

						include_once( '../includes/constants.php' );
						session_start(  );
						$settings = array( 'permissions' => array( '../license.txt', '../templates_c/', '../templates_admin_c/', '../cache/', '../cache/images/', '../cache/pages/', '../filedb/', '../files/', '../files/profile-pics/', '../includes/dbconfig.php' ), 'steps' => array( 'Permissions', 'DB Setup', 'Finish' ) );
						main(  );
?>
