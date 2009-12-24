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
	        
	        if($this->_request->getNamespace() != 'default' || isset($m['namespaces']['default'])){
	            if(isset($m['namespaces'][$this->_request->getNamespace()]['affect'])){
	                $affect = $m['namespaces'][$this->_request->getNamespace()]['affect'];
	                $class = ($affect == 'class') ? $this->_request->getNamespace().'_'.$m['class'] : $m['class'];
	                $action = ($affect == 'action') ? $this->_request->getNamespace().'_'.$this->_request->getAction() : $this->_request->getAction();
	                $default_action = ($affect == 'action') ? $this->_request->getNamespace().'_'.$m['default_action'] : $m['default_action'];
	            }else{
	                $class = $m['class'];
	                $action = $this->_request->getAction();
	                $default_action = $m['default_action'];
	            }
	        }else{
	            $class = $m['class'];
	            $action = $this->_request->getAction();
	            $default_action = $m['default_action'];
	        }
	        
	        $class = $m['class'];
            $action = $this->_request->getAction();
	        
	        if(is_file($m['directory'].$class.'.class.php')){
	            
	            if(!class_exists($class)){
	                include($m['directory'].$class.'.class.php');
                }
                
                if(!$this->_use_checking || ($this->_use_checking && class_exists($class))){
	                
	                if($this->_use_checking){
	                    
	                    $actions = get_class_methods($class);
	                    
	                    if(!in_array($action, $actions)){
	                        
	                        if($action == $default_action){
	                            throw new QuinceException("Class '".$class."' does not contain required action: ".$action);
                            }
                            
                            if(!in_array($default_action, $actions)){
                                throw new QuinceException("Class '".$class."' does not contain required action: ".$action.", nor the module default action: ".$default_action.".");
                            }else{
                                $this->_request->setAction($default_action);
                            }
	                    }
	                    
	                }
	                
	                $o = new $class;
	                
	                if($this->_use_checking){
	                
	                    if(!is_callable(array($o, $action))){
                            throw new QuinceException("Method '".$action."' of class '".$class."' is either private or protected, and cannot be called.");
                        }
                    
                    }
	                
	                $vars = $this->_request->getRequestVariables();
	                $args = array('get'=>$vars, 'post'=>$_POST);
	                
	                try{
	                    
	                    // set up charsets and content types
	                    if($this->_request->getNamespace() == 'default' && !isset($m['namespaces']['default'])){
    	                    $this->_request->setCharset(Quince::$default_charset);
    	                    $this->_request->setContentType(Quince::$default_content_type);
    	                }else{
    	                    if(isset($m['namespaces'][$this->_request->getNamespace()])){
    	                        if(isset($m['namespaces'][$this->_request->getNamespace()]['charset']) && $m['namespaces'][$this->_request->getNamespace()]['charset']) $this->_request->setCharset($m['namespaces'][$this->_request->getNamespace()]['charset']);
    	                        if(isset($m['namespaces'][$this->_request->getNamespace()]['content_type']) && $m['namespaces'][$this->_request->getNamespace()]['content_type']) $this->_request->setContentType($m['namespaces'][$this->_request->getNamespace()]['content_type']);
	                        }else{
	                            $this->_request->setCharset(Quince::$default_charset);
        	                    $this->_request->setContentType(Quince::$default_content_type);
	                        }
    	                }
    	                
    	                // pass on extra info in the module config
    	                $this->_request->setMetas($m['metas']);
    	                if(isset($m['namespaces'][$this->_request->getNamespace()]['meta']) && is_array($m['namespaces'][$this->_request->getNamespace()]['meta'])){
	                        foreach($m['namespaces'][$this->_request->getNamespace()]['meta'] as $k => $v){
	                            $this->_request->setMeta($k, $v);
	                        }
	                    }
    	                $this->_request->setMeta('_module_longname', $m['longname']);
    	                $this->_request->setMeta('_module_identifier', $m['identifier']);
    	                $this->_request->setMeta('_php_class', $class);
    	                
    	                
    	                // make the request object available to the action itself
    	                if(!$this->_use_checking || ($this->_use_checking && in_array('setCurrentRequest', $actions))){
    	                    $o->setCurrentRequest($this->_request);
    	                }
    	                
	                    // call the function. If it forwards, this will be the last line that gets executed
	                    $result = call_user_func_array(array($o, $action), $args);
	                    
	                    return $result;
	                    
	                }catch(Exception $e){
                        throw $e;
    	            }
	                
	            }else{
	                throw new QuinceException("File ".$class.".class.php does not contain required class: ".$class);
	            }
	        }else{
	            throw new QuinceException("Module '{$this->_request->getModule()}' does not contain required class file: ".$m['directory'].$class.'.class.php');
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
    protected $_content_type = null;
    protected $_charset = null;
    protected $_is_alias = false;
    protected $_request_variables = array();
    protected $_metas = array();
    protected $_result = null;
    
    final public function getModificationsEnabled($m){
        return $this->_modifications_allowed;
    }
    
    final public function setModificationsEnabled($m){
        $this->_modifications_enabled = (bool) $m;
    }
    
    final public function getModule(){
        return $this->_module;
    }
    
    final public function setModule($m){
        $this->_module = $m;
    }
    
    final public function getAction(){
        return $this->_action;
    }
    
    final public function setAction($a){
        $this->_action = $a;
    }
    
    final public function getDomain(){
        return $this->_domain;
    }
    
    final public function getHyperlinkDomain(){
        if($this->_namespace == 'default'){
            return $this->_domain;
        }else{
            return $this->_domain.$this->_namespace.':';
        }
    }
    
    final public function setDomain($d){
        $this->_domain = $d;
    }
    
    final public function getRequestString(){
        return $this->_request_string;
    }
    
    final public function getHyperlinkRequestString(){
        if($this->_namespace == 'default'){
            return $this->_request_string;
        }else{
            // return $this->_request_string.$this->_namespace.':';
            if($this->_request_string){
                return $this->_request_string;
            }else{
                return 'index';
            }
        }
    }
    
    final public function setRequestString($r){
        
        // check for namespace
        if(Quince::$use_namespaces){
            if(preg_match('/^(([^:]+):)[^:]+$/', reset(explode("/", $r)), $matches)){
    			$r = substr($r, strlen($matches[1]));
    			$this->_namespace = $matches[2];
    		}else{
    		    $this->_namespace = 'default';
    		}
	    }
	
		if($r == 'index'){
		    $r = '';
		}
	
		$this->_request_string = $r;
    }
    
    final public function getContentType(){
        return $this->_content_type;
    }
    
    final public function setContentType($c){
        $this->_content_type = $c;
    }
    
    final public function getCharset(){
        return $this->_charset;
    }
    
    final public function setCharset($c){
        $this->_charset = $c;
    }
    
    final public function getNamespace(){
        return $this->_namespace;
    }
    
    final public function setNamespace($n){
        $this->_namespace = $n;
    }
    
    final public function getRequestVariable($n){
        return $this->_request_variables[$n];
    }
    
    final public function setRequestVariable($n, $v){
        $this->_request_variables[$n] = $v;
    }
    
    final public function getRequestVariables(){
        return $this->_request_variables;
    }
    
    final public function getIsAlias(){
        return $this->_is_alias;
    }
    
    final public function setIsAlias($b){
        $this->_is_alias = (bool) $b;
    }
    
    final public function isReady(){
        return isset($this->_module);
    }
    
    final public function getResult(){
        return $this->_result;
    }
    
    final public function setResult($result){
        $this->_result = $result;
    }
    
    final public function getMetas(){
        return $this->_metas;
    }
    
    final public function setMetas($m){
        $this->_metas = $m;
    }
    
    final public function getMeta(){
        return $this->_metas;
    }
    
    final public function setMeta($name, $value){
        $this->_metas[$name] = $value;
    }
    
}

class QuinceBase{
    
    protected $_request;
    
    protected function forward($module, $action){
        
        $e = new QuinceForwardException('');
        $e->setModule($module);
        $e->setAction($action);
        throw $e;
        
    }
    
    public function setCurrentRequest($r){
        $this->_request = $r;
    }
    
    protected function getRequest(){
        // TODO
    }
    
}

class QuinceUtilities{
    
    public static function cacheGet($name){
        // $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        // $fn = Quince::$cache_dir.substr(md5($name),0,8).'.tmp';
        $fn = Quince::$cache_dir.md5($name).'.tmp';
        if(file_exists($fn)){
            return unserialize(file_get_contents($fn));
        }else{
            return false;
        }
    }
    
    public static function cacheSet($name, $data){
        // $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        // $fn = Quince::$cache_dir.substr(md5($name),0,8).'.tmp';
        $fn = Quince::$cache_dir.md5($name).'.tmp';
        return file_put_contents($fn, serialize($data));
    }
    
    public static function cacheHas($name){
        // $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        // $fn = Quince::$cache_dir.substr(md5($name),0,8).'.tmp';
        $fn = Quince::$cache_dir.md5($name).'.tmp';
        return file_exists($fn);
    }
    
    public static function cacheClear($name){
        // $fn = QUINCE_CACHE_DIR.substr(md5($name),0,8).'.tmp';
        // $fn = Quince::$cache_dir.substr(md5($name),0,8).'.tmp';
        $fn = Quince::$cache_dir.md5($name).'.tmp';
        if(file_exists($fn)){
            return unlink($fn);
        }else{
            return false;
        }
    }
    
    public static function fetchConfig($config_file){
	    $config = self::yamlLoad($config_file);
        return $config['quince'];
	}
	
	public static function fetchModuleConfig($config_file){
	    $config = self::yamlFastLoad($config_file);
        return $config['module'];
	}
	
	public static function yamlLoad($file_name){
        if(is_file($file_name)){
            $spyc = new Spyc;
            $array = $spyc->load($file_name);
            return $array;
        }else{
            // error
            SmartestLog::getInstance('system')->log("SmartestYamlHelper tried to load non-existent file: ".$file_name, SM_LOG_WARNING);
        }
    }
    
    public static function yamlFastLoad($file_name){
        if(is_file($file_name)){
            if(self::cacheGet('syhh_'.crc32($file_name)) == md5_file($file_name)){
                return self::cacheGet('syhc_'.crc32($file_name), true);
            }else{
                self::cacheSet('syhh_'.crc32($file_name), md5_file($file_name));
                $content = self::yamlLoad($file_name);
                self::cacheSet('syhc_'.crc32($file_name), $content);
                return $content;
            }
        }else{
            throw new QuinceException("SmartestYamlHelper tried to load non-existent file: ".$file_name, SM_LOG_WARNING);
        }
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
	protected $_next_request;
	
	public static $home_dir;
	public static $cache_dir;
	public static $default_charset;
	public static $default_content_type;
	public static $use_namespaces;
	
    public function __construct($home_dir="___QUINCE_CURRENT_DIR", $config_file='quince.yml'){
        
        // this value is always checked as it's 110% essential
        if($home_dir == "___QUINCE_CURRENT_DIR"){
            $this->_home_dir = getcwd().'/';
        }else if(!is_dir(dirname($home_dir))){
            $this->handleException(new QuinceException('The specified $home_dir:'.$home_dir.' does not exist.'));
        }else{
            $this->_home_dir = $home_dir;
        }
        
        // make some values available to other classes and beyond, as constants
        self::$home_dir = $this->_home_dir;
        
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
        
        // again, make this available for other classes, not least of which QuinceUtilities
        self::$cache_dir = $this->_cache_dir;
        self::$default_charset = $config['default_charset'];
        self::$default_content_type = $config['default_content_type'];
        self::$use_namespaces = $config['use_namespaces'];
        
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
	    
	    // MultiViews support: look for URLS like index.php/module/action
		$fc_filename = basename($_SERVER['SCRIPT_FILENAME']).'/';
		$fc_filename_len = strlen($fc_filename);
		
	    $ulength = strlen($url.'/');
	    
	    // If the end of the url and the file path are the same
	    if(substr(getcwd().'/', $ulength*-1, $ulength) == $url.'/'){
	        $r->setRequestString('');
	        $r->setDomain($url.'/');
	        return $r;
	    }
	    
	    // Calculate the domain
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
        
        // Loop through the directory paths until one matches
        foreach($ds as $try){
            
            $dlen = strlen($try);
            
            if(substr($url, 0, $dlen) == $try){
                
                $r->setRequestString(substr($url, $dlen));
                $r->setDomain($try);
                
                if(substr($r->getRequestString(), 0, $fc_filename_len) == $fc_filename){
        		    $r->setDomain($r->getDomain().$fc_filename);
        		    $r->setRequestString(substr($r->getRequestString(), $fc_filename_len));
        		}
                
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
	        $result = $a->execute();
	        return $result;
	    // Catch forwards
	    }catch(QuinceForwardException $e){
	        if($this->_num_forwards < 10){
                $r->setModule($e->getModule());
                $r->setAction($e->getAction());
                ++$this->_num_forwards;
                // if the next action is also a forward, nothing will be returned, and so on.
                // Something is returned only once an action is called that doesn't forward, but the whole cycle still triggered from here so we still have to use 'return'
                return $this->doAction($r);
            }else{
                $this->handleException(new QuinceException("Quince has detected too many forwards. The maximum number is 10."));
            }
        // Catch other "real" exceptions
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
	        $this->_next_request = $this->processRequest($url);
        }catch(QuinceException $e){
            $this->handleException($e);
        }
	    
	    // Scan the modules
	    try{
	        $this->scanModules();
        }catch(QuinceException $e){
            $this->handleException($e);
        }
	    
	    $rs = $this->_next_request->getRequestString();
	    
	    // Next, is the request an alias?
	    // if so, give the request object its associated module/action
	    // First, check the alias shortcuts cache
	    if(isset($this->alias_shortcuts[$rs])){
	        
	        $this->_next_request->setModule($this->alias_shortcuts[$rs]['module']);
	        $this->_next_request->setAction($this->alias_shortcuts[$rs]['action']);
	        $this->_next_request->setIsAlias(true);
	        
	        foreach($this->alias_shortcuts[$rs]['url_vars'] as $n => $v){
	            $this->_next_request->setRequestVariable($n, $v);
	        }
	        
	    }
	    
	    // the request might still be an alias
	    foreach($this->raw_aliases as $alias){
	        
	        // Aliases that do not use url vars
	        if($alias['url'] == '/'.$rs){
	            $this->_next_request->setModule($alias['module']);
	            $this->_next_request->setAction($alias['action']);
	            $this->_next_request->setIsAlias(true);
	            $this->alias_shortcuts[$rs] = array();
	            $this->alias_shortcuts[$rs]['module'] = $alias['module'];
	            $this->alias_shortcuts[$rs]['action'] = $alias['action'];
	            $this->alias_shortcuts[$rs]['url_vars'] = array();
	            break;
	        }
	        
	        // Aliases that do:
	        $regex = '/^'.preg_replace('/\/(:|\$)([\w_]+)/i', "/([^\/]+)", QuinceUtilities::excapeRegexCharacters($alias['url'])).'\/?$/';
	        
	        if(preg_match($regex, '/'.$rs, $matches)){
	            
	            preg_match_all('/\/(:|\$)([\w_]+)/i', $alias['url'], $arg_matches);
    	        $argnames = $arg_matches[2];
	            
	            array_shift($matches);
	            $argvalues = ($matches);
	            
	            $this->alias_shortcuts[$rs] = array();
	            $this->alias_shortcuts[$rs]['module'] = $alias['module'];
	            $this->alias_shortcuts[$rs]['action'] = $alias['action'];
	            $this->alias_shortcuts[$rs]['url_vars'] = array();
	            
	            $this->_next_request->setModule($alias['module']);
	            $this->_next_request->setAction($alias['action']);
	            $this->_next_request->setIsAlias(true);
	            
	            foreach($argnames as $i => $n){
    	            $this->_next_request->setRequestVariable($n, $argvalues[$i]);
    	            $this->alias_shortcuts[$rs]['url_vars'][$n] = $argvalues[$i];
    	        }
    	        
    	        break;
    	        
	        }
	    }
	    
	    // if not, then see if the url maps directly onto a module/action
	    if(!$this->_next_request->isReady()){
	        $u = explode('/', $rs);
	        if(count($u) < 3){
	            $module = $u[0];
	            if(in_array($module, $this->module_shortnames)){
	                $this->_next_request->setModule($module);
	                if(isset($u[1])){
	                    $this->_next_request->setAction($u[1]);
                    }
                }else{
                    // default module, default action
                    $this->_next_request->setModule($this->_default_module_name);
                }
            }else{
                // default module, default action
                $this->_next_request->setModule($this->_default_module_name);
            }
	    }
	    
	    // Next quickly add any GET and POST variables to the request object
	    if(count($_GET)){
	        foreach($_GET as $n => $v){
	            $this->_next_request->setRequestVariable($n, $v);
	        }
	    }
	    
	    if(count($_POST)){
	        foreach($_POST as $n => $v){
	            $this->_next_request->setRequestVariable($n, $v);
	        }
	    }
	    
	    if($do_action){
	    
	        try{
	            $result = $this->doAction($this->_next_request);
	            $this->_next_request->setResult($result);
	        }catch(QuinceException $e){
                $this->handleException($e);
            }
        
        }
        
        QuinceUtilities::cacheSet('alias_url_shortcuts', $this->alias_shortcuts);
        
        return $this->_next_request;
	    
	}
	
	public function handleException(Exception $e){
	    
	    switch($this->_exception_handling){
	        case 'die':
	        die($e->getMessage());
	        case 'return':
	        return $e;
	        case 'ignore':
	        return;
	        case 'throw':
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
        // $ignore, as its name would suggest, is ignored
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