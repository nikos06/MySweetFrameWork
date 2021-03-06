<?php namespace t0t1\mysfw\module;
use t0t1\mysfw;

class request extends mysfw\frame\dna{
    protected $_params = [];

    protected $_post= array();
    protected $_server= array();
    protected $_files= array();
    protected $_method;

    protected $_filter= null;

    protected $_defaults= array(
        'request:INPUT_GET'=> array(),
        'request:INPUT_POST'=> array(),
        'request:INPUT_SERVER'=> array(),
        'request:INPUT_FILES'=> array(),
        'request:method' => null
    );

    protected function _get_ready(){
        $this->_params['query'] = $this->inform('request:INPUT_GET')?:$_GET;
        $this->_params['post'] = $this->inform('request:INPUT_POST')?:$_POST;
        $this->_server = $this->inform('request:INPUT_SERVER')?:$_SERVER;
        $this->_params['files'] = $this->inform('request:INPUT_FILES')?:$_FILES;
        $this->_method = $this->inform('request:method')?:$_SERVER['REQUEST_METHOD'];
        $this->_filter = $this->pop('filter');
    }

    public function get_method() {return $this->_method;}

    public function get_raw_input(){
        return file_get_contents("php://input");
    }

    public function get_query($k=null, array $filters=null) {
        if(empty($k)  and $k!==0) return $this->_params['query'];
        if($this->has_query($k)){
            return $this->_filter->apply($this->_params['query'][$k],$filters);
        }
        return false;
    }

    public function has_query($k){
        return array_key_exists($k,$this->_params['query']);
    }

    public function get_post($k=null, array $filters=null) {
        if(empty($k)  and $k!==0) return $this->_params['post'];
        if($this->has_post($k)){
            return $this->_filter->apply($this->_params['post'][$k],$filters);
        }
        return false;
    }

    public function has_post($k){
        return array_key_exists($k,$this->_params['post']);
    }

    public function get_server($k=null, array $filters=null) {
        if(empty($k)  and $k!==0) return $this->_server;
        if($this->has_server($k)){
            return $this->_filter->apply($this->_server[$k],$filters);
        }
        return false;
    }

    public function has_server($k){
        return array_key_exists($k,$this->_server);
    }

    public function get_files($k=null, array $filters=null) {
        if(empty($k)  and $k!==0) return $this->_params['files'];
        if($this->has_file($k)){
            return $this->_filter->apply($this->_params['files'][$k],$filters);
        }
        return false;
    }

    public function has_file($k){
        return array_key_exists($k,$this->_params['files']);
    }

    public function is_post(){
        return $this->get_server('REQUEST_METHOD',array('trim'))=='POST';
    }

    public function is_put(){
        return $this->get_server('REQUEST_METHOD',array('trim'))=='PUT';
    }

    public function is_get(){
        return $this->get_server('REQUEST_METHOD',array('trim'))=='GET';
    }

    public function is_delete(){
        return $this->get_server('REQUEST_METHOD',array('trim'))=='DELETE';
    }

    public function is_patch(){
        return $this->get_server('REQUEST_METHOD',array('trim'))=='PATCH';
    }

    public function accepts_json(){
        return ( false !== strpos($this->get_server('HTTP_ACCEPT',array('trim')),'application/json'));
    }

    public function unset_query($name) {
     return $this->_unset('query', $name);
    }

    protected function _unset($nature, $name) {
     switch($nature) {
      case 'files':
      case 'post':
      case 'query':
      case 'internal':
       $this->report_debug("Unsetting `$nature` parameter `$name`");
       unset($this->_params[$nature][$name]);
       return $this;
     }
    }

    // XXX draft
    public function set($nature, $name, $value) {
     switch($nature) {
      case 'files':
      case 'post':
      case 'query':
      case 'internal':
       $this->report_debug("Setting `$nature` parameter `$name` to `".print_r($value, true)."`");
       $this->_params[$nature][$name] = $value;
       return $this;
     }
    }

    // XXX draft
    public function get($nature, $name = null) {
     switch($nature) {
      case 'files':
      case 'post':
      case 'query':
      case 'internal':
       if($name) {
        if(isset($this->_params[$nature][$name]))
         return $this->_params[$nature][$name];
        return null;
       }
       return $this->_params[$nature];
     }
    }
}
