<?php
if(isset($_GET["action"])) {
	$action = $_GET["action"];
	switch($action) {
		case 'check-version':
			checkVersion(true);
			break;	
	}
}

function newClass($className) {

	$numargs = func_num_args();
    if ($numargs > 1) {
        $args = func_get_args();
        $args = array_slice($args, 1);
    }else{
    	$args = null;
    }
    
	require_once (BASE_PATH.'classes/'.$className.'.class.php');
	$class = new $className;
	if($args) 
		call_user_func_array(array($class, $className), $args);
	
	return $class;
}

function formatDate($date, $format = "d/m/Y") {
    return date($format, strtotime($date));
}

function warnInstallFolder() {

	if(file_exists(BASE_PATH.'install')) {
		if(INSTALL_COMPLETE) {
			echo('<div style="width:100%; background:#FFCC00; color:#000; text-transform:uppercase; font-weight:bold; text-align:center;font-family: sans-serif; padding:5px;"><span>Security Warning:</span> Make sure to delete or rename the install/ directory!</div>');
		}
	}
}

function warnLicenseError($errors) {
	echo('<div style="width:100%; background:red; color:#fff; text-transform:uppercase; font-weight:bold; text-align:center;font-family: sans-serif; padding:5px;"><span>License Error:</span> '.$errors.' You can modify your license key from within the settings...</div>');

}

function checkVersion($remote=null) {

	if($remote) {
		$forceCheck = isset($_SESSION['last_version_check']) && (time() - $_SESSION['last_version_check'] > 3600);
		if(!$forceCheck && isset($_SESSION["version_check"]) && (json_decode($_SESSION["version_check"]) != NULL)) {
			echo $_SESSION["version_check"];
		}else{
			$func = newClass("Functions");
			$_SESSION["version_check"] = $func->getUrlContents(APP_VERSION_URL);
			$_SESSION['last_version_check'] = time();
			echo $_SESSION["version_check"];
		}
		exit;
	}else{
		$output .= '<div id="prismosoft_app_version" style="display:none"><span class="text"></span></div>';
		$output .= '
		<script type="text/javascript">
			$(document).ready(function() {
				var version_div = $("#prismosoft_app_version");
				$.ajax({
				  url: "'.WEBSITE_URL.ADMIN_URL.'?action=check-version",
				  dataType: "json",
				  cache: true,
				  success: function(data) {
				  	if(data){
						version_div.find("span.text").html(data.msg);
						version_div.show();
					}
				  }
				});
	
			});
		</script>';
		echo $output;
	}
	
}
?>