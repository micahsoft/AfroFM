<?
$tpl->assign('website_name', WEBSITE_NAME );
$tpl->assign('website_url', WEBSITE_URL );
$tpl->assign('admin_url', WEBSITE_URL.ADMIN_PATH );
$tpl->assign('meta_keywords', WEBSITE_KEYWORDS );
$tpl->assign('meta_description', WEBSITE_DESCRIPTION );
$tpl->assign('fb_admins', FB_ADMINS );
$tpl->assign('fb_app_id', FB_APP_ID );
$tpl->assign('fb_page_url', FB_PAGE_URL );
if(defined("FB_APP_ID") && FB_APP_ID != "") {
	$tpl->assign('fb_js', "<div id='fb-root'></div>
	<script>
	(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = '//connect.facebook.net/en_US/all.js#xfbml=1&appId=".FB_APP_ID."';
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	</script>");
}
$tpl->assign('tracking_code', TRACKING_CODE );
$tpl->assign('js_vars', $js_vars );
$tpl->assign('active_template', $session->template );
$tpl->assign('active_theme', $session->theme );
$tpl->assign('theme_base', $theme_base );
$tpl->assign('tpl_base', $tpl_base );
$tpl->assign('js_base', $js_base );
$tpl->assign('url_base', "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/");
$tpl->assign('form', $form);
$tpl->assign('session', $session);
$tpl->assign('func', $func);
$tpl->assign('dbase', $dbase);
$tpl->assign('image', $image);
$tpl->assign('POST', $_POST);
$tpl->assign('GET', $_GET);

?>