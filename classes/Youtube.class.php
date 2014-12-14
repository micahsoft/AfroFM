<?php
# YouTube PHP class
# used for embedding videos as well as video screenies on web page without single line of HTML code
#
# Dedicated to my beloved brother FILIP. Rest in peace!
#
# by Avram, www.avramovic.info
class Youtube
{
    private $id = NULL;
    /**
     * Set YouTube ID explicitly
     *
     * This method sets YouTube ID explicitly. It checks if the ID is in good format. If yes it will set it
     * and return true, and if not - it will return false
     *
     * @access public
     * @param string $id YouTube ID
     * @return boolean Whether the ID has been set successfully
     */
    public function setID($id)
    {
        if (preg_match('/([A-Za-z0-9_-]+)/', $url, $matches)) {
            $this->id = $id;
            return true;
        } else
            return false;
    }
    /**
     * Get string representation of YouTube ID
     *
     * This method returns YouTube video ID if any. Otherwise returns null.
     *
     * @access public
     * @return string YouTube video ID if any, otherwise null
     */
    public function getID()
    {
        return $this->id;
    }
    public function url2id($url)
    {
        $aux  = explode("?", $url);
        $aux2 = explode("&", $aux[1]);
        foreach ($aux2 as $campo => $valor) {
            $aux3 = explode("=", $valor);
            if ($aux3[0] == 'v')
                $video = $aux3[1];
        }
        return $this->id = $video;
    }
    /**
     * Parse YouTube URL and return video ID.
     *
     * This method sreturnns YouTube video ID if any. Otherwise returns null.
     *
     * @access public
     * @static
     * @param string $url URL of YouTube video in any of most commonly used forms
     * @return string YouTube video ID if any, otherwise null
     */
    public static function parseURL($url)
    {
        if (preg_match('/watch\?v\=([A-Za-z0-9_-]+)/', $url, $matches))
            return $matches[1];
        else
            return false;
    }
    /**
     * Get YouTube video HTML embed code
     *
     * This method returns HTML code which is used to embed YouTube video in page
     *
     * @access public
     * @param string $url YouTube video URL. If this cannot be parsed it will be used as video ID. It can be omitted
     * @param integer $width Width of embedded video, in pixels. Defaults to 425
     * @param integer $height Height of embedded video, in pixels. Defaults to 344
     * @return string HTML code which is used to embed YouTube video in page
     */
    public function embedVideo($url = null, $width = 425, $height = 344)
    {
        if ($url == null)
            $videoid = $this->id;
        else {
            $videoid = YouTube::parseURL($url);
            if (!$videoid)
                $videoid = $url;
        }
        return '<object width="' . $width . '" height="' . $height . '"><param name="movie" value="http://www.youtube.com/v/' . $videoid . '?rel=0&fs=1&loop=0&autoplay=1"></param><param name="wmode" value="transparent"></param><param name="allowFullScreen" value="true"><embed src="http://www.youtube.com/v/' . $videoid . '?rel=0&fs=1&loop=0&autoplay=1" allowfullscreen="true" type="application/x-shockwave-flash" wmode="transparent" width="' . $width . '" height="' . $height . '"></embed></object>';
    }
    /**
     * Get URL of YouTube video screenshot
     *
     * This method returns URL of YouTube video screenshot. It can get one of three screenshots defined by YouTube
     *
     * @access public
     * @param string $url YouTube video URL. If this cannot be parsed it will be used as video ID. It can be omitted
     * @param integer $imgid Number of screenshot to be returned. It can be 1, 2 or 3
     * @return string URL of YouTube video screenshot
     */
    public function getImgURL($url = null, $imgid = 1)
    {
        if ($url == null)
            $videoid = $this->id;
        else {
            $videoid = YouTube::parseURL($url);
            if (!$videoid)
                $videoid = $url;
        }
        return "http://img.youtube.com/vi/$videoid/$imgid.jpg";
    }
    /**
     * Get URL of YouTube video screenshot
     *
     * This method returns URL of YouTube video screenshot. It can get one of three screenshots defined by YouTube
     * DEPRECATED! Use getImgURL instead.
     *
     * @deprecated
     * @see getImgURL
     * @access public
     * @param string $url YouTube video URL. If this cannot be parsed it will be used as video ID. It can be omitted
     * @param integer $imgid Number of screenshot to be returned. It can be 1, 2 or 3
     * @return string URL of YouTube video screenshot
     */
    public function getImg($url = null, $imgid = 1)
    {
        return getImgURL($url, $imgid);
    }
    /**
     * Get YouTube screenshot HTML embed code
     *
     * This method returns HTML code which is used to embed YouTube video screenshot in page
     *
     * @access public
     * @param string $url YouTube video URL. If this cannot be parsed it will be used as video ID
     * @param integer $imgid Number of screenshot to be returned. It can be 1, 2 or 3
     * @param string $alt Alternate text of the screenshot
     * @return string HTML code which embeds YouTube video screenshot
     */
    public function showImg($url = null, $imgid = 1, $alt = 'Video screenshot')
    {
        return "<img src='" . $this->getImgURL($url, $imgid) . "' width='130' height='97' border='0' alt='" . $alt . "' title='" . $alt . "' />";
    }
    public function search($query, $max = 5)
    {
        if ($_SERVER['HTTP_X_FORWARD_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $feedURL = 'http://gdata.youtube.com/feeds/base/videos?q=' . $query . '&client=ytapi-youtube-search&format=5&restriction=' . $ip . '&max-results=' . $max . '&v=2';
        $sxml    = simplexml_load_file($feedURL);
        $i       = 0;
        foreach ($sxml->entry as $entry) {
            $details                 = $entry->content;
            $info[$i]["title"]       = $entry->title;
            $aux                     = explode($info[$i]["title"], $details);
            $aux2                    = explode("<a", $aux[0]);
            $aux3                    = explode('href="', $aux2[1]);
            $aux4                    = explode('&', $aux3[1]);
            $info[$i]["link"]        = $aux4[0];
            $details_notags          = strip_tags($details);
            $texto                   = explode("From", $details_notags);
            $info[$i]["description"] = $texto[0];
            $aux                     = explode("Views:", $texto[1]);
            $aux2                    = explode(" ", $aux[1]);
            $info[$i]["views"]       = $aux2[0];
            $aux                     = explode("Time:", $texto[1]);
            $aux2                    = explode("More", $aux[1]);
            $info[$i]["time"]        = $aux2[0];
            $imgs                    = strip_tags($details, '<img>');
            $aux                     = explode("<img", $imgs);
            array_shift($aux);
            array_shift($aux);
            $aux2 = explode("gif\">", $aux[4]);
            array_pop($aux);
            $aux3   = $aux2[0] . 'gif">';
            $aux[]  = $aux3;
            $images = '';
            foreach ($aux as $campo => $valor) {
                $images .= '<img' . $valor;
            }
            $info[$i]["images"] = $images;
            $i++;
        }
        return $info;
    }
    public function linkSearch($query, $max = 5)
    {
        if ($_SERVER['HTTP_X_FORWARD_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $feedURL = 'http://gdata.youtube.com/feeds/base/videos?q=' . rawurlencode($query) . '&client=ytapi-youtube-search&format=5&restriction=' . $ip . '&max-results=' . $max . '&v=2';

        $sxml    = simplexml_load_file($feedURL);
        $i       = 0;
        foreach ($sxml->entry as $entry) {
            $details  = $entry->content;
            $aux      = explode($entry->title, $details);
            $aux2     = explode("<a", $aux[0]);
            $aux3     = explode('href="', $aux2[1]);
            $aux4     = explode('&', $aux3[1]);
            $info[$i] = $aux4[0];
            $i++;
        }
        return $info;
    }
    
}