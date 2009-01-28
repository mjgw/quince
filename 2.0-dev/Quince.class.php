<?php

class QuinceAction{
    
    protected $_request;
    protected $_use_checking;
    
    public function __construct($r, $use_checking=true){
        $this->_request = $r;
        $this->_use_checking = $use_checking;
    }
    
    public function execute(){
        
        if($m = QuinceUtilities::cacheGet('module_config_'.$this->_request->getModule())){
	        
	        if(!$this->_request->getAction()){
	            $this->_request->setAction($m['default_action']);
	        }
	        
	        if(is_file($m['directory'].$m['class'].'.class.php')){
	            
	            if(!class_exists($m['class'])){
	                include($m['directory'].$m['class'].'.class.php');
                }
                
                if(!$this->_use_checking || ($this->_use_checking && class_exists($m['class']))){
	                
	                if($this->_use_checking){
	                    
	                    $actions = get_class_methods($m['class']);
	                    
	                    if(!in_array($this->_request->getAction(), $actions)){
	                        
	                        if($this->_request->getAction() == $m['default_action']){
	                            throw new QuinceException("Class '{$m['class']}' does not contain required action: ".$this->_request->getAction());
                            }
                            
                            if(!in_array($m['default_action'], $actions)){
                                throw new QuinceException("Class '{$m['class']}' does not contain required action: ".$this->_request->getAction().", nor the module default action: {$m['default_action']}");
                            }else{
                                $this->_request->setAction($m['default_action']);
                            }
	                    }
	                }
	                
	                $o = new $m['class'];
	                $vars = $this->_request->getRequestVariables();
	                $args = array('get'=>$vars, 'post'=>$_POST);
	                
	                try{
	                    $result = call_user_func_array(array($o, $this->_request->getAction()), $args);
	                    return $result;
	                }catch(Exception $e){
                        throw $e;
    	            }
	                
	            }else{
	                throw new QuinceException("File {$m['class']}.class.php does not contain required class: ".$m['class']);
	            }
	        }else{
	            throw new QuinceException("Module '{$this->_request->getModule()}' does not contain required class file: ".$m['directory'].$m['class'].'.class.php');
	        }
	        
	    }else{
	        throw new QuinceException("Could not retrieve module info for module '{$this->_request->getModule()}' from cache.");
	    }
        
    }

}

class QuinceRequest{
    
    protected $_module;
    protected $_action;
    protected $_domain;
    protected $_namespace;
    protected $_request_string;
    protected $_is_alias = false;
    protected $_request_variables = array();
    
    public function getModule(){
        return $this->_module;
    }
    
    public function setModule($m){
        $this->_module = $m;
    }
    
    public function getAction(){
        return $this->_action;
    }
    
    public function setAction($a){
        $this->_action = $a;
    }
    
    public function getDomain(){
        return $this->_domain;
    }
    
    public function setDomain($d){
        $this->_domain = $d;
    }
    
    public function getRequestString(){
        return $this->_request_string;
    }
    
    public function setRequestString($r){
        
        // check for namespace
        // TODO: This needs to only happen if the use_namespaces config directive is set to true
        if(preg_match('/^(([^:]+):)[^:]+$/', reset(explode("/", $r)), $matches)){
			$r = substr($r, strlen($matches[1]));
			$this->_namespace = $matches[2];
		}
        
        $this->_request_string = $r;
    }
    
    public function getNamespace(){
        return $this->_namespace;
    }
    
    public function setNamespace($n){
        $this->_namespace = $n;
    }
    
    public function getRequestVariable($n){
        return $this->_request_variables[$n];
    }
    
    public function setRequestVariable($n, $v){
        $this->_request_variables[$n] = $v;
    }
    
    public function getRequestVariables(){
        return $this->_request_variables;
    }
    
    public function getIsAlias(){
        return $this->_is_alias;
    }
    
    public function setIsAlias($b){
        $this->_is_alias = (bool) $b;
    }
    
    public function isReady(){
        return isset($this->_module);
    }
    
}

class QuinceBase{
    
    protected function forward($module, $action){
        
        $e = new QuinceForwardException('');
        $e->setModule($module);
        $e->setAction($action);
        throw $e;
        
    }
    
}

class QuinceUtilities{
    
    public static function cacheGet($name){
        $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        if(file_exists($fn)){
            return unserialize(file_get_contents($fn));
        }else{
            return false;
        }
    }
    
    public static function cacheSet($name, $data){
        $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        return file_put_contents($fn, serialize($data));
    }
    
    public static function cacheHas($name){
        $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        return file_exists($fn);
    }
    
    public static function cacheClear($name){
        $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        if(file_exists($fn)){
            return unlink($fn);
        }else{
            return false;
        }
    }
    
    public static function fetchConfig($config_file){
	    $config = Spyc::YAMLLoad($config_file);
        return $config['quince'];
	}
	
	public static function fetchModuleConfig($config_file){
	    $config = Spyc::YAMLLoad($config_file);
        return $config['module'];
	}
    
    public static function dirContents($dir, $t=0){
        
        $files = array();

	    $res = opendir($dir);
	    $str = '';

		while (false !== ($file = readdir($res))) {

    		if($file{0} != '.'){
    		    
    		    $files[] = is_dir($dir.$file) ? $dir.utf8_encode($file).'/' : $dir.utf8_encode($file);
    		    
    		}

		}
		
		closedir($res);

		return $files;
        
    }
    
    public static function excapeRegexCharacters($s){
        
        $regexp = str_replace('/', '\/', $s);
		$regexp = str_replace('|', '\|', $regexp);
		$regexp = str_replace('[', '\[', $regexp);
		$regexp = str_replace(']', '\]', $regexp);
		$regexp = str_replace('{', '\{', $regexp);
		$regexp = str_replace('}', '\}', $regexp);
		$regexp = str_replace('.', '\.', $regexp);
        
        return $regexp;
        
    }
    
    public static function stripSlashesFromArray($value){
		return is_array($value) ? array_map(array('QuinceUtilities','stripSlashesFromArray'), $value) : utf8_encode(stripslashes($value));
	}
    
}

class QuinceException extends Exception{}

class QuinceForwardException extends QuinceException{

    protected $_module;
    protected $_action;
    
    public function getModule(){
        return $this->_module;
    }
    
    public function setModule($m){
        $this->_module = $m;
    }
    
    public function getAction(){
        return $this->_action;
    }
    
    public function setAction($a){
        $this->_action = $a;
    }

}

class Quince{
    
    const CURRENT_URL = '___QUINCE_CURRENT_URL';
    const CURRENT_DIR = '___QUINCE_CURRENT_DIR';
    
    protected $_home_dir;
    protected $_cache_dir;
	protected $_module_dirs = array();
	protected $_module_conf;
	protected $_default_module_name;
	protected $_use_checking;
	protected $_request_class;
	protected $_exception_handling;
	protected $_existing_modules;
	protected $_num_forwards = 0;

    public function __construct($home_dir="___QUINCE_CURRENT_DIR", $config_file='quince.yml'){
        
        if($home_dir == "___QUINCE_CURRENT_DIR" || !is_dir(dirname($home_dir))){
            $this->_home_dir = getcwd().'/';
        }else{
            $this->_home_dir = $home_dir;
        }
        
        // make some values available to other classes and beyond, as constants
        if(!defined('QUINCE_HOME_DIR')){
            define('QUINCE_HOME_DIR', $this->_home_dir);
        }
        
        // load quince configuration
        $config = QuinceUtilities::fetchConfig($config_file);
        $this->_exception_handling = $config['exception_handling'];
        $this->_module_dirs = $config['modules']['storage'];
        $this->_module_conf = $config['modules']['config'];
        $this->_request_class = $config['request_class'];
        $this->_default_module_name = $config['default_module'];
        $this->_use_checking = $config['use_checking'];
        $this->_use_namespaces = $config['use_namespaces'];
        $this->_cache_dir = realpath($this->_home_dir.$config['cache_dir']).'/';
        if(!defined('QUINCE_CACHE_DIR')){
            define('QUINCE_CACHE_DIR', $this->_cache_dir);
        }
        
    }
    
    private function removeQueryString($url){
	    
	    $hash = md5($url);
	    
	    if(!isset($this->_non_query_urls[$hash])){
	        if($nq = strpos($url, '?')){
    	        $url = substr($url, 0, $nq);
    	    }
    	    
    	    $this->_non_query_urls[$hash] = $url;
    	    
	    }
	    
	    return $this->_non_query_urls[$hash];
	    
	}
    
    public function processRequest($url){
	    
	    $r = new $this->_request_class;
	    
	    $ulength = strlen($url.'/');
	    
	    if(substr(getcwd().'/', $ulength*-1, $ulength) == $url.'/'){
	        $r->setRequestString('');
	        $r->setDomain($url.'/');
	        return $r;
	    }
	    
	    $hdp = explode('/', getcwd());
        array_shift($hdp);
        $hdp = array_reverse($hdp);
        
        $argnum = 1;
        $count = (count($hdp)-1);
        $ds = array();
        
        for($i=0;$i<$count;++$i){
            $ds[] = '/'.implode('/', array_reverse(array_slice($hdp, 0, ($argnum * -1)))).'/';
            ++$argnum;
        }
        
        $ds = array_reverse($ds);
        $r->setRequestString(substr($url, 1));
        $r->setDomain('/');
        
        foreach($ds as $try){
            
            $dlen = strlen($try);
            
            if(substr($url, 0, $dlen) == $try){
                
                $r->setRequestString(substr($url, $dlen));
                $r->setDomain($try);
                print_r($r);
                
                return $r;
            }
        }
        
        return $r;
	}
	
	public function getNewModulesList(){
	    
	    $all_modules = array();
        
        foreach($this->_module_dirs as $m){
            $dirs = QuinceUtilities::dirContents($this->_home_dir.$m);
            $all_modules = array_merge($all_modules, $dirs);
        }
        
        return $all_modules;
        
	}
	
	protected function scanModules(){
	    
	    // first, find modules.
        $this->_existing_modules = is_array($this->_existing_modules) ? $this->_existing_modules : $this->getNewModulesList();
        
        // now a tricky bit - the cache
        
        $new_hash = md5(implode(':', $this->_existing_modules));
        $old_hash = QuinceUtilities::cacheGet('all_modules_hash');
        
        if(!$old_hash || $old_hash != $new_hash){
            
            QuinceUtilities::cacheClear('all_modules_config_hash');
            QuinceUtilities::cacheSet('all_modules_hash', $new_hash);
            
        }
        
        // Modules have changed, try a shallow traverse of modules, just checking hashes of config files
        $amch = '';
        
        foreach($this->_existing_modules as $m){
            $cf = $m.$this->_module_conf;
            if(is_file($cf)){
                $amch.=md5_file($cf);
            }
        }
        
        $new_mcoh = md5($amch);
        $old_mcoh = QuinceUtilities::cacheGet('all_modules_config_hash');
        
        if(!$old_mcoh || $old_mcoh != $new_mcoh){
            
            // somewhere the module configs have changed. A deep traversal is in order.
            
            $aliases = array();
            $module_shortnames = array();
            $module_names = array();
            
            foreach($this->_existing_modules as $m){
                $cf = $m.$this->_module_conf;
                if(is_file($cf)){
                    
                    $conf = QuinceUtilities::fetchModuleConfig($cf);
                    $conf['directory'] = $m;
                    
                    if(!isset($module_names[$conf['shortname']])){
                    
                        // cache whole module conf
                        QuinceUtilities::cacheSet('module_config_'.$conf['shortname'], $conf);
                        
                        // add module shortname to list, so we know it's real
                        $module_shortnames[] = $conf['shortname'];
                    
                        // get aliases
                        if(isset($conf['aliases']) && is_array($conf['aliases'])){
                            foreach($conf['aliases'] as &$a){
                                $a['module'] = $conf['shortname'];
                            }
                            $aliases = array_merge($aliases, $conf['aliases']);
                        }
                    }
                    
                }
            }
            
            // write one long list of the aliases to cache
            QuinceUtilities::cacheSet('all_aliases', $aliases);
            $this->raw_aliases = $aliases;
            
            // write one long list of the aliases to cache
            QuinceUtilities::cacheSet('module_shortnames', $module_shortnames);
            $this->module_shortnames = $module_shortnames;
            
            // as configs have changed, alias shortcuts need to be refreshed
            QuinceUtilities::cacheClear('alias_url_shortcuts');
            $this->alias_shortcuts = array();
            
            // write the new hash of the module configs to cache
            QuinceUtilities::cacheSet('all_modules_config_hash', $new_mcoh);
            
        }else{
            $this->raw_aliases = QuinceUtilities::cacheGet('all_aliases');
            $this->alias_shortcuts = QuinceUtilities::cacheGet('alias_url_shortcuts');
            $this->module_shortnames = QuinceUtilities::cacheGet('module_shortnames');
        }
	    
	    if(!in_array($this->_default_module_name, $this->module_shortnames)){
            $this->handleException(new QuinceException("The specified default module, '{$this->_default_module_name}', does not exist."));
        }
	    
	}
	
	public function doAction($r){
	    
	    $a = new QuinceAction($r, $this->_use_checking);
	    
	    try{
	        return $a->execute();
	    }catch(QuinceForwardException $e){
	        if($this->_num_forwards < 99){
                $r->setModule($e->getModule());
                $r->setAction($e->getAction());
                ++$this->_num_forwards;
                $this->doAction($r);
            }else{
                $this->handleException(new QuinceException("Quince has detected too many forwards. The maximum number is 99."));
            }
        }catch(Exception $e){
            $this->handleException($e);
        }
	    
	}
	
	public function dispatch($url='___QUINCE_CURRENT_URL', $do_action=true){
        
        if($url == "___QUINCE_CURRENT_URL"){
	        $url = $_SERVER['REQUEST_URI'];
	        $url_is_current_request = true;
	    }else{
	        $url_is_current_request = false;
	    }
	    
	    $url = $this->removeQueryString($url);
	    
	    try{
	        $r = $this->processRequest($url);
        }catch(QuinceException $e){
            $this->handleException($e);
        }
	    
	    // Scan the modules
	    try{
	        $this->scanModules();
        }catch(QuinceException $e){
            $this->handleException($e);
        }
	    
	    $rs = $r->getRequestString();
	    
	    // Next, is the request an alias?
	    // if so, give the request object its associated module/action
	    // First, check the alias shortcuts cache
	    if(isset($this->alias_shortcuts[$rs])){
	        
	        $r->setModule($this->alias_shortcuts[$rs]['module']);
	        $r->setAction($this->alias_shortcuts[$rs]['action']);
	        $r->setIsAlias(true);
	        
	        foreach($this->alias_shortcuts[$rs]['url_vars'] as $n => $v){
	            $r->setRequestVariable($n, $v);
	        }
	        
	    }
	    
	    // the request might still be an alias
	    foreach($this->raw_aliases as $alias){
	        
	        // Aliases that do not use url vars
	        if($alias['url'] == '/'.$rs){
	            $r->setModule($alias['module']);
	            $r->setAction($alias['action']);
	            $r->setIsAlias(true);
	            $this->alias_shortcuts[$rs] = array();
	            $this->alias_shortcuts[$rs]['module'] = $alias['module'];
	            $this->alias_shortcuts[$rs]['action'] = $alias['action'];
	            $this->alias_shortcuts[$rs]['url_vars'] = array();
	            break;
	        }
	        
	        // Aliases that do:
	        $argnames = $matches[2];
	        
	        $regex = '/^'.preg_replace('/\/(:|\$)([\w_]+)/i', "/([^\/]+)", QuinceUtilities::excapeRegexCharacters($alias['url'])).'\/?$/';
	        
	        if(preg_match($regex, '/'.$rs, $matches)){
	            
	            array_shift($matches);
	            $argvalues = ($matches);
	            
	            $this->alias_shortcuts[$rs] = array();
	            $this->alias_shortcuts[$rs]['module'] = $alias['module'];
	            $this->alias_shortcuts[$rs]['action'] = $alias['action'];
	            $this->alias_shortcuts[$rs]['url_vars'] = array();
	            
	            $r->setModule($alias['module']);
	            $r->setAction($alias['action']);
	            $r->setIsAlias(true);
	            
	            foreach($argnames as $i => $n){
    	            $r->setRequestVariable($n, $argvalues[$i]);
    	            $this->alias_shortcuts[$rs]['url_vars'][$n] = $argvalues[$i];
    	        }
    	        
    	        break;
    	        
	        }
	    }
	    
	    // if not, then see if the url maps directly onto a module/action
	    if(!$r->isReady()){
	        $u = explode('/', $rs);
	        if(count($u) < 3){
	            $module = $u[0];
	            if(in_array($module, $this->module_shortnames)){
	                $r->setModule($module);
	                if(isset($u[1])){
	                    $r->setAction($u[1]);
                    }
                }else{
                    // default module, default action
                    $r->setModule($this->_default_module_name);
                }
            }else{
                // default module, default action
                $r->setModule($this->_default_module_name);
            }
	    }
	    
	    if($do_action){
	    
	        try{
	            $result = $this->doAction($r);
	        }catch(QuinceException $e){
                $this->handleException($e);
            }
        
        }
        
        QuinceUtilities::cacheSet('alias_url_shortcuts', $this->alias_shortcuts);
        
        return $r;
	    
	}
	
	public function handleException(Exception $e){
	    
	    switch($this->_exception_handling){
	        case 'die':
	        die($e->getMessage());
	        case 'return':
	        return $e;
	        default:
	        throw $e;
	    }
	    
	}
	
	public function cleanUp(){
	    
	    unset($this->module_shortnames);
	    unset($this->alias_shortcuts);
	    unset($this->raw_aliases);
	    unset($this->_default_module_name);
	    
	}
	
}

class QuinceLegacy extends Quince{
    
    protected $_request;
    protected $_content;
    protected $_module;
    
    public function __construct($ignore, $dispatch_now=true){
        parent::__construct(Quince::CURRENT_DIR, 'quince.yml');
        if($dispatch_now){
            $this->dispatch();
        }
    }
    
    public function dispatch(){
        $this->_request = parent::dispatch(Quince::CURRENT_URL);
        $this->_module = QuinceUtilities::cacheGet('module_config_'.$this->_request());
    }
    
    public function performAction(){
        $this->_content = $this->doAction($this->_request);
    }
    
    public function getContent(){
        return $this->_content;
    }
    
    public function getMethodName(){
        return $this->_request->getAction();
    }
    
    public function getSectionName(){
        return $this->_request->getAction();
    }
    
    public function getNameSpace(){
        return $this->_request->getNamespace();
    }
    
    public function getClassName(){
        return $this->_module['class'];
    }
    
    public function getIsAlias(){
        return $this->_request->getIsAlias();
    }
    
}