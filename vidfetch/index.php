<?php
/* * *****************************************************************************
 *
 * ******************************************************************************
 *      Website:    http://www.vidfetch.com
 *
 *      Copyright:  (c) 20011 - Baseapp Systems
 *
 * ******************************************************************************
 *  VERION HISTORY:
 *
 *      v0.4 [15.3.2011]  - Rewrite for VidFetch
 *      v0.3 [22.3.2010]  - Rewrite
 *      v0.2 [5.3.2010]   - Added Exception Handling
 *      v0.1 [15.11.2009] - Initial Version
 *
 * ******************************************************************************
 *  DESCRIPTION:
 *
 *      NOTE: See www.vidfetch.com for the most recent version of this script
 *      and its usage.
 *
 * ******************************************************************************
 */


$siteURL = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';

$bookmarklet = 'javascript:document.location.href=\'' . $siteURL . '?url=\'+escape(document.location.href)';

function getApplet($jarFile, $className, $params = array(), $width=1, $height=1, $name='japplet') {
    $retVal = "";

    $useApplet = 0;
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    if (stristr($user_agent, "konqueror") || stristr($user_agent, "macintosh") || stristr($user_agent, "opera")) {
        $useApplet = 1;
        $retVal = sprintf('<applet name="%s" id="%s" archive="%s" code="%s" width="%s" height="%s" MAYSCRIPT >', $name, $name, $jarFile, $className, $width, $height);
    } else {
        if (strstr($user_agent, "MSIE")) {
            $retVal = sprintf('<object  name="%s" id="%s" classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93" style="border-width:0;" codebase="http://java.sun.com/products/plugin/autodl/jinstall-1_4_1-windows-i586.cab#version=1,4,1"  width= "%s" height= "%s">', $name, $name, $width, $height);
        } else {
            $retVal = sprintf('<object  name="%s" id="%s" type="application/x-java-applet;version=1.4.1" width= "%s" height= "%s">', $name, $name, $width, $height);
        }

        $params['archive'] = $jarFile;
        $params['code'] = $className;
        $params['mayscript'] = 'true';
        $params['scriptable'] = 'true';
        $params['name'] = $name;
    }

    foreach ($params as $var => $val) {
        $retVal .= sprintf('<param name="%s" value="%s">', $var, $val);
    }

    $retVal .= 'It appears you do not have Java installed or it is disabled on your system.<br />
                    Please download it <a href="http://www.java.com/getjava/" class="link" target="_blank">here</a>';
    if ($useApplet == 1) {
        $retVal .= '</applet>';
    } else {
        $retVal .= '</object>';
    }

    return $retVal;
}

if (isset($_GET['url']) && !empty($_GET['url'])) {
    if (strstr($_GET['url'], 'http')) {
        $url = $_GET['url'];
    } else {
        $error = "Invalid url please enter valid video url.";
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Your Site Title</title>
        <meta name="description" content="Download and Convert videos directly from Youtube, Google, Metacafe and more. Simply copy and paste." />
      <meta name="keywords" content="freevid, keep, vid, keep vid, download, direct, help, rip, copy, convert , youtube to mo3 ,  save, video, stream, youtube, yuotube, toutube, uoutube, houtube" />

        <!-- Framework CSS -->
        <link rel="stylesheet" href="assets/screen.css" type="text/css" media="screen, projection">

        <!--[if lt IE 8]><link rel="stylesheet" href="assets/ie.css" type="text/css" media="screen, projection"><![endif]-->

        <link rel="stylesheet" href="assets/theme.css" type="text/css" media="screen, projection">
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
    </head>
    <body>
        <div id="content">
            <div id="content-upper">
                <div id="content-upper-bottom">
                    <div class="container">
                        <?php
                        if (!empty($url)) {
                        ?>
                            <script type="text/javascript">


                                loaderVisible = true;
                                VFResult = "";
                                VFProxy = "";
                                VFThumbnail = "";

                                function vidfetchSearching()
                                {
                                    $('#japplet').width(1);
                                    $('#japplet').height(1);
                                    $('#vidfetchSearching').show();
                                }

                                function vidfetchAppend(type,name,index,title)
                                {

                                    if(loaderVisible)
                                    {
                                        $('#vidfetchSearching').hide();
                                        $('#vidfetchLinks').show();
                                        loaderVisible = false;
                                    }


                                    // does name have extension else append it

                                    if(name.indexOf(type) < 0)
                                    {
                                            name += " <b>("+type+")</b>";
                                    }

                                    var proxyLink = VFProxy+index+"/"+title+"."+type.toLowerCase();


                                    if(type == 'MP3' || type == 'AAC')
                                    {
                                        $('#vidfetchLinks ul.audio').append('<li class="type'+type+'"><a href="'+proxyLink+'"><span>'+name+'</span></a></li>');
                                    } else {
                                        $('#vidfetchLinks ul.video').append('<li class="type'+type+'"><a href="'+proxyLink+'"><span>'+name+'</span></a></li>');
                                    }
                                    
                                    vidfetchDone();

                                }

                                function vidfetchDone()
                                {

									if($('#vidfetchLinks ul.audio').length > 0 || $('#vidfetchLinks ul.video').length > 0) {
	                                    $('#vidfetchSearching').hide();
	                                    $('#vidfetchError').hide();
	                                    $('#java-required').hide();
	                                }
                                }

                                function vidfetchProxy(url)
                                {

                                }

                                function vidfetchError()
                                {
                                    $('#vidfetchSearching').hide();
                                    $('#vidfetchError').show();
                                }

                            </script>


                            <div id="vidfetchSearching" style="display:none;">
                                <center>
                                    <img src="assets/loader.gif">
                                </center>
                            </div>


                            <div id="vidfetchLinks" style="display:none;">
                                <div class="productHeadingType4">
                                    <div class="previewWarning">When downloading please do not close/exit this page.</div>
                                    <div id="java-required">
                            			Please make sure you have java installed. Select Allow when prompted.
                        			</div>
                        			
                                    <div class="vimage" style="display:none;"><img src=""></div>
                                    <div class="cfloat1"><div class="cfloat2">

                                            <ul class="video"></ul>
                                            <ul class="audio"></ul>

                                    </div></div>
                                    <div class="clear"></div>
                                </div>
                            </div>

                            <div id="vidfetchLoader">
                                <center>
                                <?php
                                echo getApplet('http://vidfetch.com/java/VidFetchApplet.signed.jar', 'VidFetchApplet.class', array('url' => $url, 'userAgent' => $_SERVER['HTTP_USER_AGENT']), 100, 100);
                                ?>
                            </center>
                        </div>

                        <div id="vidfetchError"  style="display:none;">
                                <span style="color: rgb(204, 51, 51);"><b>No Videos found or site not supported.</b><br />
                                Please make sure you selected yes when prompted for '<b>Always trust content from the publisher</b>'.</span>
                        </div>

                        <?php } ?>
                        <?php if (isset($error) && !empty($error)) {?>
                                <center><span class="invalidUrl"><?php echo $error; ?> </span></center><br/>
                        <?php }?>

                        </div>
                        


                    </div>
                </div>

            </div>
		</div>
    </body>
</html>
