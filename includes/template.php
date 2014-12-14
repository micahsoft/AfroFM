<?
class Template extends Smarty {

  function Template() {
	global $session;
	
    $this->Smarty();
	if(!$session->adminMode){
    	$this->template_dir = BASE_PATH . 'templates/' .$session->endPath . ACTIVE_TEMPLATE . '/';
    	$this->compile_dir  = BASE_PATH . 'templates_c/';
    }else{
    	$this->template_dir = BASE_PATH . 'templates/'.$session->endPath;
    	$this->compile_dir  = BASE_PATH . 'templates_admin_c/';
    }
    
    $this->config_dir   = '';
    $this->cache_dir    = '';
    $this->caching = false;
  }
}
?>