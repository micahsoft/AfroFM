<?php

class Ytconv{

    private $url = 'http://ytconv.com/';
	private $check = 'check.php';
	private $grabber = 'grabber.php';
	private $converter = 'converter.php';
	private $sizecheck = 'check-size.php';
		
    //Search
    function check($url, $hq=1) {
		$curl = newClass('Curl');
		$curl->referer = $this->url;

   		$data = 'url='.$url.'&hq='.$hq;	
		$res = $curl->post($this->url.$this->check, $data);
		return $res;	
    }
    
    function grab($info) {
    	//check info: OKCHECK|trolfuCImFY|534525|James Blunt - 1973 (HQ Audio with lyrics)|4|089556efc3adadd95809b20451f33cb0.mp4|22449639
    	$infoArr = explode('|', $info);

    	$status = @$infoArr[0];
    	if($status == "OKCHECK") {
    		$v = @$infoArr[1];
    		$t = @$infoArr[2];
    		$title = @$infoArr[3];
    		$hq = @$infoArr[4];
    		$filename = @$infoArr[5];
    		$filesize = @$infoArr[6];
    		    	
			$curl = newClass('Curl');
			$curl->referer = $this->url;
	
	   		$data = 'v='.$v.'&t='.$t.'&title='.$title.'&hq='.$hq;
			$res = $curl->post($this->url.$this->grabber, $data);
		}else{
			$res = "CHECK_ERROR";
		}
		return $res;	    
    }
    
    function convert($info) {
    	//grab info: OKLOAD|09ce161fedbb74f053169c9be659b988.mp4|Mia Martina -  Turn It Up (Feat. Belly) [HD]|4|19QP_MoXGp4
    	$infoArr = explode('|', $info);
	
    	$status = @$infoArr[0];
    	if($status == "OKLOAD") {

    		$filename = @$infoArr[1];
    		$title = @$infoArr[2];
    		$hq = @$infoArr[3];
    		$v = @$infoArr[4];
    		    	
			$curl = newClass('Curl');
			$curl->referer = $this->url;
	
	   		$data = 'file='.$filename.'&title='.$title.'&hq='.$hq.'&v='.$v;	
			$res = $curl->post($this->url.$this->converter, $data);
			
			$res = $this->result($res);
		}else{
			$res = 'GRAB_ERROR';
		}
		return $res;	    
    }
    
    function result($info) {
    	//OKCONVERT|19QP_MoXGp4|Mia Martina -  Turn It Up (Feat. Belly) [HD]|download2.php?v=19QP_MoXGp4&title=Mia+Martina+-++Turn+It+Up+%28Feat.+Belly%29+%5BHD%5D&hq=4|Original MP4|03:08|20.89
    	$infoArr = explode('|', $info);
 	
    	$status = $infoArr[0];
    	if($status == "OKCONVERT") {
    		$v = @$infoArr[1];
    		$title = @$infoArr[2];
    		$download_url = $this->url.@$infoArr[3];
    		$hq = @$infoArr[4];
    		$length = @$infoArr[5];
    		$filesize = @$infoArr[6];
    		
    		$res = $download_url.'||'.$hq.'||'.$length.'||'.$filesize;
    		
    	}else{
    		$res = 'CONVERT_ERROR';
    	}
		return $res;    		    	   	    
    }
    
    function checksize($fname, $fsize, $hq) {
    
    	//fname:09ce161fedbb74f053169c9be659b988.mp4
		//fsize:21908666
		//hq:4
		
		$curl = newClass('Curl');
		$curl->referer = $this->url;
	
	   	$data = 'fname='.$fname.'&fsize='.$fsize.'&hq='.$hq;	
		$res = $curl->get($this->url.$this->sizecheck.'?'.$data);
		preg_match('/\<h3\>(.+?)\<\/h3\>/', $res, $match);
		return $match[1];	
    }
          
}

?>
