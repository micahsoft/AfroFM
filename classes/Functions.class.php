<?

class Functions {

    function removeDash($s) {
      $s = str_replace("_", ' ', $s);
      return $s;
    }

	function setting($key, $default_value, $group = 'Other Settings', $type = null) {
		
		if($key == "" || !defined($key)) {
		
			if($type == null) {
				$type = (is_numeric($val) ? 'numeric' : 'text');
			}
			$sData = array(
				'name'=>ucwords(strtolower(str_replace("_", " ", $key))),
				'key'=>$key,
				'value'=>$default_value,
				'group'=>$group,
				'type'=>$type
			);
			$set = newClass('Settings');
			$set->addSetting($sData);
			
			return $default_value;
			
		}else{ 
			return constant($key);
		}
	
	}
	
    function deleteFiles($path) {
            //using the opendir function
            $dir_handle = @opendir($path) or die("Unable to open $path");

            //running the while loop
            while ($file = readdir($dir_handle))
            {
                    if($file!="." && $file!="..") {
                            unlink($path.$file);
                    }
            }
            //closing the directory
            closedir($dir_handle);

    }

    function containFiles($path) {
            //using the opendir function
            $dir_handle = @opendir($path) or die("Unable to open $path");

            //running the while loop
            while ($file = readdir($dir_handle))
            {
                    if($file!="." && $file!="..") {
                            return true;
                    }
            }
            //closing the directory
            closedir($dir_handle);

            return false;
    }

    function assignQueryValues(){
		
        $trailCount = 0;
        $rootFile = (ADMIN_MODE) ? ADMIN_URL : 'index.php';
        $tmp = explode($rootFile, $_SERVER['PHP_SELF']);
        $pos = strpos($_SERVER['REQUEST_URI'], $tmp[0]) + strlen($tmp[0]);
		
        if ($pos === false) {
            $querystring = "";
        } else {
            $querystring = substr($_SERVER['REQUEST_URI'],$pos);

            $queryData = explode("/",$querystring);
            $startI = (ADMIN_MODE) ? 1 : 0; 
            for($i=0 ; $i<sizeof($queryData) ; $i++) {

                if($i == $startI) {
                    $pos = strpos($queryData[$i], ":");
                    $pos2 = strpos($queryData[$i], "?");
                    $pos3 = strpos($queryData[$i], "index");
                    $pos4 = strpos($queryData[$i], "admin");
                    if($pos === false && $pos2 === false && $pos3 === false && $pos4 === false)
                        $_GET['l'] = str_replace("_", ".", $queryData[$i]);
                    if($pos3 !== false)
                        $_GET['l'] = "home";
                }
                $keyVal = explode(":", $queryData[$i]);
                $key = $keyVal[0];
                $val = $keyVal[1];

                if($val != "")
                    $_GET[$key] = $val;
                else if($val == "" && $key != "" && str_replace("_", ".", $key) != $_GET['l']) {
                    $_GET["trailingParam"][$trailCount] = $key;
                    $trailCount++;
                }
                    
            }
        }
        if($_GET["l"] == "")
            $_GET["l"] = "home";
        
    }

    function pluralize($count, $string) {
        if($count > 1)
            return $string.'s';
        else
            return $string;
    }

    function formatDate($date, $format = "d/m/Y") {
        return date($format, strtotime($date));
    }

    function getExtension($filename) {

        if (!empty($filename)) {
            $filename = strtolower($filename);
            $extArray = explode(".", $filename);
            $p = count($extArray) - 1;
            $extension = $extArray[$p];
            return $extension;
        } else {
            return false;
        }
    }

	function getLanguages() {
		global $session;
		$dir    = 'includes/languages/site/';
		$files = scandir($dir);
		$files = array_slice($files, 2);
		$i = 0;
		foreach($files as $file) {
			if($file != 'admin' && substr($file, 0, 1) != "_") {
				$file = substr($file, 0, sizeof($file)-5);
				$languages[$i]['code'] = $file;
				$languages[$i]['name'] = ucfirst($file);
				if($querystring != '')
					$languages[$i]['link'] = $this->link("?".$querystring."&lang=".$file, '', '', true);
				else 
					$languages[$i]['link'] = $this->link("?lang=".$file, '', '', true);
				$i++;
			}
		}
		return $languages;
	}

    function lang($string, $output = "return") {
	
        $definePrefix = '[LANG]';

        if(defined($definePrefix.$string))
            $result = constant($definePrefix.$string);
        else{
            $result = $string;
            /*
            if(isset($_COOKIE['clang']) && $_COOKIE['clang'] == DEFAULT_LANGUAGE) {
            	$languageFile = 'includes/languages/'.DEFAULT_LANGUAGE.'.php';
            	$define = 'define("'.$definePrefix.addslashes($string).'","'.addslashes($string).'");'."\r\n";
            	$this->writeToFile($languageFile, $define, 'a');
            	chmod($languageFile, 0777);
            	define($definePrefix.$string,$string);
            }
            */
		}
        if($output == "echo")
            echo $result;
        else
            return $result;
    }
    
    
    function redirect($location, $params = "", $t = "", $fullLink = false, $mode="") {
    	global $session;

        if($fullLink) {
            header('Location: '.$location);
            exit();
        }
        
        $adminUrl = $session->adminUrl;
        if($mode != "") {
        	if($mode == 'admin') {
        		$adminUrl = (SEO_ENABLED) ? ADMIN_SEO_URL : ADMIN_URL;
        	}else{
        		$adminUrl = '';
        	}
        }
        
        if(SEO_ENABLED) {
            $location = $this->seoURL($location);
            $seoparams = $this->seoParams($params);
            header('Location: '.WEBSITE_ROOT.$adminUrl.$location.$seoparams.$t);
        }else{
        	$t = ($t != "") ? "&t=".$t : ""; 
            if($params != "")
                $params = "&".$params.$t;
            header('Location: '.WEBSITE_ROOT.$adminUrl.'?l='.$location.$params);
        }
    }

    function link($l, $q="", $t="", $fullLink = false, $mode="") {
		global $session;
		
		if($fullLink) {
            return $l;
        }
        
        $adminUrl = $session->adminUrl;
        if($mode != "") {
        	if($mode == 'admin') {
        		$adminUrl = (SEO_ENABLED) ? ADMIN_SEO_URL : ADMIN_URL;
        	}else{
        		$adminUrl = '';
        	}
        }
        
        if(SEO_ENABLED) {
            $retLoc = $this->seoUrl($l);
            $retParams = $this->seoParams($q);

            return WEBSITE_ROOT.$adminUrl.$retLoc.$retParams.$t;

        }else{
        
        	$t = ($t != "") ? "&t=".$t : ""; 
            if(isset($q))
                $query = "&".$q.$t;
            else
                $query = "";

            return WEBSITE_ROOT.$adminUrl."?l=".$l.$query;
        }
    }

    function seoURL($location){
        if($location == "")
            return "";
        
        $retLoc = str_replace(".", "_", $location);
        $retLoc .= "/";

        return $retLoc;
    }

    function seoParams($params){
        if($params != "") {
            $params = str_replace("&", "/", $params);
            $params = str_replace("=", ":", $params);
            $params .= "/";
            return $params;
        }else{
            return "";
        }
    }
    
    function seoTitle($s) {
      $c = array (' ','-','/','\\',',','.','#',':',';','\'','"','[',']','{',
          '}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+');

      $s = str_replace($c, '_', $s);

      $s = preg_replace(
            array('/-+/',
                  '/-$/',
                  '/-ytmsinternsignature/'),
            array('_',
                  '',
                  'ytmsinternsignature') ,
            $s);
      return $s;
    }

    function implodeGet() {
        $first = true;
        $output = '';
        foreach($_GET as $key => $value) {
            if($key != "l") {
                if ($first) {
                    $output = $key.'='.$value;
                    $first = false;
                } else {
                    $output .= '&'.$key.'='.$value;
                }
            }
        }
        return $output;
    }

    function removeEmptyVal($array) {
        foreach($array as $key => $value) {
            if($value == "") {
                unset($array[$key]);
            }
        }
        $new_array = array_values($array);

        return $new_array;
    }

    function xml2Array($xmlUrl, $node = '', $get_attributes=1, $priority = 'tag')
    {
       $xml = new DOMDocument;
       $xml->load($xmlUrl);
       $contents = $xml->saveXML();

       if(!$contents) return array();

       if(!function_exists('xml_parser_create'))
       {
          //print "'xml_parser_create()' function not found!";
          return array( );
       }

       //Get the XML parser of PHP - PHP must have this module for the parser to work
       $parser = xml_parser_create('');
       xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
       xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
       xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
       $result = xml_parse_into_struct($parser, trim($contents), $xml_values);
       xml_parser_free($parser);

       if(!$xml_values || $result == 0) {
           return array();//Hmm...
       }

       //Initializations
       $xml_array = array();
       $parents = array();
       $opened_tags = array();
       $arr = array();

       $current = &$xml_array; //Refference

       //Go through the tags.
       $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
       foreach($xml_values as $data)
       {
          unset($attributes,$value);//Remove existing values, or there will be trouble

          //This command will extract these variables into the foreach scope
          // tag(string), type(string), level(int), attributes(array).
          extract($data);//We could use the array by itself, but this cooler.

          $result = array();
          $attributes_data = array();

          if(isset($value))
          {
             if($priority == 'tag') $result = $value;
             else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
          }

          //Set the attributes too.
          if(isset($attributes) and $get_attributes)
          {
             foreach($attributes as $attr => $val)
             {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
             }
          }

          //See tag status and do the needed.
          if($type == "open")
          {//The starting of the tag '<tag>'
             $parent[$level-1] = &$current;

             if(!is_array($current) or (!in_array($tag, array_keys($current))))
             { //Insert New tag
                $current[$tag] = $result;

                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                   $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

             }

             else
             { //There was another element with the same tag name
                if(isset($current[$tag][0]))
                {//If there is a 0th element it is already an array
                   $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                   $repeated_tag_index[$tag.'_'.$level]++;
                }

                else
                {//This section will make the value an array if multiple tags with the same name appear together
                   $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                   $repeated_tag_index[$tag.'_'.$level] = 2;

                   if(isset($current[$tag.'_attr']))
                   { //The attribute of the last(0th) tag must be moved as well
                      $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                      unset($current[$tag.'_attr']);
                   }
                }

                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
             }
          }

          elseif($type == "complete")
          { //Tags that ends in 1 line '<tag />'
             //See if the key is already taken.
             if(!isset($current[$tag]))
             { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                if($priority == 'tag' and $attributes_data)
                   $current[$tag. '_attr'] = $attributes_data;
             }

             else
             { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag]))
                {//If it is already an array...
                   // ...push the new element into that array.
                   $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                   if($priority == 'tag' and $get_attributes and $attributes_data)
                   {
                      $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                   }

                   $repeated_tag_index[$tag.'_'.$level]++;
                }

                else
                { //If it is not an array...
                   $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                   $repeated_tag_index[$tag.'_'.$level] = 1;

                   if($priority == 'tag' and $get_attributes)
                   {
                      if(isset($current[$tag.'_attr']))
                      { //The attribute of the last(0th) tag must be moved as well
                         $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                         unset($current[$tag.'_attr']);
                      }

                      if($attributes_data)
                      {
                         $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                      }
                   }

                   $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
             }
          }

          elseif($type == 'close')
          { //End of tag '</tag>'
             $current = &$parent[$level-1];
          }
       }


       if($node != "") {
            $this->sliceArrayByNode($xml_array, $node);
       }
       return $xml_array;
    }

    function convertXmlObjToArr( $obj, &$arr )
    {
        $children = $obj->children();
        $executed = false;
        foreach ($children as $elementName => $node)
        {
            if( array_key_exists( $elementName , $arr ) )
            {
                if(array_key_exists( 0 ,$arr[$elementName] ) )
                {
                    $i = count($arr[$elementName]);
                    $this->convertXmlObjToArr ($node, $arr[$elementName][$i]);
                }
                else
                {
                    $tmp = $arr[$elementName];
                    $arr[$elementName] = array();
                    $arr[$elementName][0] = $tmp;
                    $i = count($arr[$elementName]);
                    $this->convertXmlObjToArr($node, $arr[$elementName][$i]);
                }
            }
            else
            {
                $arr[$elementName] = array();
                $this->convertXmlObjToArr($node, $arr[$elementName]);
            }
            $executed = true;
        }
        if(!$executed&&$children->getName()=="")
        {
            $arr = (String)$obj;
        }

        return ;
    }

    function sliceArrayByNode(&$arr, $node) {

        if($node != "") {
            $nodeArr = explode("|", $node);
            for($i = 0 ; $i < sizeof($nodeArr) ; $i++)
                $arr = $arr[$nodeArr[$i]];
        }

    }

    function getUrlContents($url, $max = -1){

        if(function_exists('curl_init')) {
            $crl = curl_init();
            $timeout = 5;
            curl_setopt ($crl, CURLOPT_URL,$url);
            curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($crl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
            $ret = curl_exec($crl);
            curl_close($crl);
            return $ret;

        }else{
            echo "Cannot access remote url <br>".$url."<br><br>Make sure 'allow_url_fopen' is set to true or enable CURL!";
        }
    }

    function writeFile($filename, $filedata, $flag = 'w') {

        $fh = fopen($filename, $flag) or die("can't open file: ".$filename);
        fwrite($fh, $filedata);
        fclose($fh);
    }

    function ftpMkdir($path, $mode){

        // set up basic connection
        $conn_id = ftp_connect(FTP_HOST);

        // login with username and password
        $login_result = ftp_login($conn_id, FTP_USER, FTP_PASS);

        $dir=split("/", $path);
        $path="";
        $ret = true;

        for ($i=0;$i<count($dir);$i++){
            $path.="/".$dir[$i];
            if(!ftp_chdir($conn_id,$path)){
                ftp_chdir($conn_id,"/");
                if(!ftp_mkdir($conn_id,$path)){
                    $ret=false;
                    break;
                } else {
                    ftp_chmod($conn_id, $mode, $path);
                }
            }
        }
        
        // close the connection
        ftp_close($conn_id);
        return $ret;
     }
     
     
  	 function json_encode($a=false) {
        if (is_null($a)) return 'null';
        if ($a === false) return 'false';
        if ($a === true) return 'true';
        if (is_scalar($a)) {
        	if (is_float($a)) {
        		// Always use "." for floats.
        		return floatval(str_replace(",", ".", strval($a)));
      		}

      		if (is_string($a)) {
        		static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        		return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      		}else
        		return $a;
    	}
    	$isList = true;
    	for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
      		if (key($a) !== $i) {
       			$isList = false;
        		break;
      		}
    	}
    	$result = array();
    	if ($isList){
      		foreach ($a as $v) $result[] = $this->json_encode($v);
      		return '[' . join(',', $result) . ']';
		}else{
      		foreach ($a as $k => $v) $result[] = $this->json_encode($k).':'.$this->json_encode($v);
      		return '{' . join(',', $result) . '}';
    	}
  	}
  	
  	
  	function subPage(&$content, $start=null, $end=null) {
    
    	if($start) {
    		$tmp = explode($start, $content);
    		$ncontent = $tmp[1];
    	}
    	if($end) {
    		$tmp = explode($end, $ncontent);
    		$ncontent = $tmp[0];
    	}
    	return $ncontent;
    
    }
    
 
	function country_currency( $country, $amount = 0 ) {
		$bc = strtoupper($country); // Set this to your country
		$currency_before = '';
		$currency_after = '';
		
		if( $bc == 'GB' || $bc == 'IE' || $bc == 'CY' ) $currency_before = '&pound;';
		if( $bc == 'AT' || $bc == 'BE' || $bc == 'FI' || $bc == 'FR' || 
		    $bc == 'DE' || $bc == 'GR' || $bc == 'GP' || $bc == 'IT' ||
		    $bc == 'LU' || $bc == 'NL' || $bc == 'PT' || $bc == 'SI' ||
		    $bc == 'ES') $currency_before = '&euro;';
		if( $bc == 'BR' ) $currency_before = 'R$';
		if( $bc == 'CN' || $bc == 'JP' ) $currency_before = '&yen;';
		if( $bc == 'CR' ) $currency_before = '&cent;';
		if( $bc == 'HR' ) $currency_after = ' kn';
		if( $bc == 'CZ' ) $currency_after = ' kc';
		if( $bc == 'DK' ) $currency_before = 'DKK ';
		if( $bc == 'EE' ) $currency_after = ' EEK';
		if( $bc == 'HK' ) $currency_before = 'HK$';
		if( $bc == 'HU' ) $currency_after = ' Ft';
		if( $bc == 'IS' || $bc == 'SE' ) $currency_after = ' kr';
		if( $bc == 'IN' ) $currency_before = 'Rs. ';
		if( $bc == 'ID' ) $currency_before = 'Rp. ';
		if( $bc == 'IL' ) $currency_after = ' NIS';
		if( $bc == 'LV' ) $currency_before = 'Ls ';
		if( $bc == 'LT' ) $currency_after = ' Lt';
		if( $bc == 'MY' ) $currency_before = 'RM';
		if( $bc == 'MT' ) $currency_before = 'Lm';
		if( $bc == 'NO' ) $currency_before = 'kr ';
		if( $bc == 'PH' ) $currency_before = 'PHP';
		if( $bc == 'PL' ) $currency_after = ' z';
		if( $bc == 'RO' ) $currency_after = ' lei';
		if( $bc == 'RU' ) $currency_before = 'RUB';
		if( $bc == 'SK' ) $currency_after = ' Sk';
		if( $bc == 'ZA' ) $currency_before = 'R ';
		if( $bc == 'KR' ) $currency_before = 'W';
		if( $bc == 'CH' ) $currency_before = 'SFr. ';
		if( $bc == 'SY' ) $currency_after = ' SYP';
		if( $bc == 'TH' ) $currency_after = ' Bt';
		if( $bc == 'TT' ) $currency_before = 'TT$';
		if( $bc == 'TR' ) $currency_after = ' TL';
		if( $bc == 'AE' ) $currency_before = 'Dhs. ';
		if( $bc == 'VE' ) $currency_before = 'Bs. ';
		
		if( $currency_before == '' && $currency_after == '' ) $currency_before = '$';
		
		return $currency_before . number_format( $amount, 2 ) . $currency_after;
	}

   
    function cacheExists($cacheFile, $force=false, $cachedays=null) {
    	if(!CACHE_ENABLED) return false;
    	if(!defined(FILEDB_CACHETIME))
    		define("FILEDB_CACHETIME", 7);
    	$cacheFile = BASE_PATH.DIR_FILEDB.$cacheFile;
    	if($cachedays == null)
    		$cachedays = (86400 * FILEDB_CACHETIME);
    		
     	return (file_exists($cacheFile) && (time() - $cachedays < filemtime($cacheFile))) && (!isset($_GET["force"])); 
    }
    
    function readCache($cacheFile, $force=false) {
    	if(!CACHE_ENABLED) return false;
    	$cacheFile = BASE_PATH.DIR_FILEDB.$cacheFile;
    	return implode("", file($cacheFile));
    }
    
    function writeCache($cacheFile, $content, $force=false) {
    	if(!CACHE_ENABLED && !$force) return false;
    	$cacheFile = BASE_PATH.DIR_FILEDB.$cacheFile;
    	$fh = fopen($cacheFile, 'w') or die("can't open file: ".$cacheFile);
		fwrite($fh, $content);
		fclose($fh);
		chmod($cacheFile, 0755);
    }  

    function writeToFile($filename, $content, $mode='w+') {
      $handle = fopen(BASE_PATH.$filename, $mode) or die("can't open file: ".BASE_PATH.$filename);
      fwrite($handle, $content);
      fclose($handle);
    }

};


?>