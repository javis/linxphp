<?php
/*
 * Linx PHP Framework
 * Author: Javier Arias. *
 * Licensed under MIT License.
 */

/*
TIP:
codigo para emular la reescritura que hace el htaccess.

$helper=new Url();	
if (preg_match('ï¿½'.($helper->get_application_path()).'/(?!index\\.php\\?route=)(.*)ï¿½i', $_SERVER['HTTP_REFERER'])) {	
	$referer_url=new Url(preg_replace('ï¿½'.($helper->get_application_path()).'/(.*)ï¿½i', $helper->get_application_path().'/index.php?route=$1', ($_SERVER['HTTP_REFERER'])));				
}
*/

/**
 * Url Helper. This class allows you to write and read url easily.
 *
 * with this class you can:
 * * know what's the current url requested
 * * create new application urls easily
 * * get and change the parameters of a url
 * * output your urls in a friendly format
 */
class Url {
    private $_https=false;
    private $_server_name=false;
    private $_server_port=false;
    private $_request_uri=false;
    private $_params=array();
    private $_fragment=null;

    private static $_default_rewriter=null;
    
    /**
     * Factory static mehod. Useful for method chaining.
     * @return Url instance 
     */
    public static function factory(){
        $class = get_class();
        return new $class();
    }

    /**
     * sets a default rewriter class which will modify the output of all the urls printed with this class
     *@param Object $rewriter a instance of a rewriter class
     */
    public static function set_default_url_rewriter(IUrlRewriter $rewriter) {
        self::$_default_rewriter=$rewriter;
    }

    /**
     * for default it's true, if you change it to false, the instance of this class will not use the default rewriter
     */
    public $use_rewriter=true;

    /**
     * Returns the base url to the application
     */
    public function get_application_path() {
        $current_url='http';
        $current_url.=($this->_https)?'s':'';
        $current_url.='://'. $this->_server_name;
        if (!empty($this->_server_port) and $this->_server_port!=80)
            $current_url.=":".$this->_server_port;
        $current_url.=$this->_request_uri;

        $current_url=explode('/',$current_url);
        array_pop($current_url);
        $current_url=implode('/',$current_url);
        return $current_url;
    }

    /**
     *@param String $url if passed, the new instance of this class will parse and fill its properties with the new value, by default, its the current application url
     */
    function __construct($url=null) {
        if (empty($url)) {
            $this->_https=(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']=='on')?true:false;
            $this->_server_name=$_SERVER['SERVER_NAME'];
            $this->_server_port=$_SERVER['SERVER_PORT'];
            $this->_request_uri=$_SERVER['SCRIPT_NAME'];

            foreach ($_GET as $name=>$value) {
                $this->_params[$name]=$value;
            }
        }
        else {
            $url_parts=parse_url($url);
            if($url_parts) {
                
                if (isset($url_parts['host']))
                    $this->_https=($url_parts['host']=='https')?true:false;
                else
                    $this->_https=(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']=='on')?true:false;

                if (isset($url_parts['fragment']))
                $this->_fragment = $url_parts['fragment'];
                $this->_server_port=isset($url_parts['port'])?$url_parts['port']:$this->_server_port=$_SERVER['SERVER_PORT'];
                $this->_server_name=(isset($url_parts['host']))?$url_parts['host']:$_SERVER['SERVER_NAME'];
                $this->_request_uri=(isset($url_parts['path']))?str_replace('//','/',dirname($_SERVER['SCRIPT_NAME']).'/'.$url_parts['path']):$_SERVER['SCRIPT_NAME'];
                $this->_request_uri.=(isset($url_parts['query']))?'?'.$url_parts['query']:'';

                if (isset($url_parts['query'])) {
                    $parse_request=explode('?',$this->_request_uri);
                    $this->_request_uri=array_shift($parse_request);

                    $parse_request=implode($parse_request);
                    $parse_request=explode("&",$parse_request);

                    foreach ($parse_request as $param) {
                        $param=explode("=",$param);
                        $this->_params[$param[0]]='';
                        if (isset($param[1]))
                            $this->_params[$param[0]]=$param[1];
                    }
                }

            }

            elseif (preg_match('{(?:(?<protocol>https?|ftp)://(?<domain>[-A-Z0-9.]+))?(?<file>/?[-A-Z0-9+&@#/%=~_|!:,.;]*)?(?<parameters>\?[-A-Z0-9+&@#/%=~_|!:,.;]*)?}i',  ($url), $result)) {

                if (!empty($result['protocol']))
                    $this->_https=($result['protocol']=='https')?true:false;
                else
                    $this->_https=(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']=='on')?true:false;

                $this->_server_port=isset($result['port'])?$result['port']:$this->_server_port=$_SERVER['SERVER_PORT'];

                if (!empty($result['domain']) and !empty($result['file'])) {
                    $this->_server_name=$result['domain'];
                    $this->_request_uri=$result['file'];
                }
                elseif(empty($result['domain']) and !empty($result['file'])) {
                    $this->_server_name=$_SERVER['SERVER_NAME'];

                    $filepath = dirname($_SERVER['SCRIPT_NAME']);


                    $this->_request_uri=str_replace('//','/',$filepath.'/'.$result['file']);
                }
                else {
                    $this->_server_name=$_SERVER['SERVER_NAME'];
                    $this->_request_uri=$_SERVER['SCRIPT_NAME'];
                }


                $this->_request_uri.=(isset($result['parameters']))?$result['parameters']:'';
                if (isset($result['parameters'])) {
                    $parse_request=explode('?',$this->_request_uri);
                    $this->_request_uri=array_shift($parse_request);

                    $parse_request=implode($parse_request);
                    $parse_request=explode("&",$parse_request);

                    foreach ($parse_request as $param) {
                        $param=explode("=",$param);
                        $this->_params[$param[0]]='';
                        if (isset($param[1]))
                            $this->_params[$param[0]]=$param[1];
                    }
                }
            }

        }
    }
    /**
     * set the server name part of the url
     */
    public function set_server_name($name) {
        $this->_server_name=$name;
        return $this;
    }
    /**
     * get the server name part of the url
     */
    public function get_server_name() {
        return $this->_server_name;
    }

    /**
     * returns the the url as string
     */
    public function get_url(){        
        $current_url='http';
        $current_url.=($this->_https)?'s':'';
        $current_url.='://'. $this->_server_name;
        if (!empty($this->_server_port) and $this->_server_port!=80)
            $current_url.=":".$this->_server_port;
        $current_url.=$this->_request_uri;

        if (count($this->_params)>0) {
            $request='';
            foreach ($this->_params as $name=>$value) {
                if ($request=='')
                    $request.='?';
                else
                    $request.='&';

                if (is_array($value)) {
                    $value1 = '';
                    foreach($value as $valueitem) {
                        if ($value1!='')
                            $value1.="&$name"."[]=";
                        if ($name != Application::get_router_param())
                            $value1 .= urlencode($valueitem);
                        else
                            $value1 .= $valueitem;
                    }

                    $request .= $name.'[]='.$value1;
                }
                else {
                    if ($name==Application::get_router_param())
                        $request .= $name.'='.($value);
                    else
                        $request .= $name.'='.urlencode($value);
                }
            }
            $current_url.= $request;
        }
        
        if (!empty(self::$_default_rewriter) and is_object(self::$_default_rewriter) and $this->use_rewriter) {            
            $current_url=self::$_default_rewriter->rewrite($current_url);
        }

        if (trim($this->_fragment))
        $current_url.='#'.$this->_fragment;

        return $current_url;
    }

    public function set_fragment($value){
        $this->_fragment = $value;
    }

    /**
     * set the value of a url parameter
     *@param string $name the name of the parameter
     *@param string $value the new value for the paramenter
     */
    public function set_param($name,$value) {
        if (empty($value))
            $this->remove_param($name);
        else
            $this->_params[$name]=$value;
        return $this;
    }
    /**
     * get the value of a url parameter
     *@param String $name the name of the parameter
     *@param Mixed $default_value a value retourned in case the parameter is not found
     */
    public function get_param($name,$default_value=null) {
        if (!array_key_exists($name,$this->_params)) return $default_value;
        return $this->_params[$name];
    }

    /**
     * Remove all the params from the url
     */
    public function clear_params() {
        $this->_params=array();
        return $this;
    }
    /**
     * remove a specific parameter from the url
     *@param String $remove the name of the parameter to be removed
     */
    public function remove_param($remove) {
        
        if (array_key_exists($remove,$this->_params))
            unset($this->_params[$remove]);
        return $this;
    }

    /**
     * returns true if the parameter specified is set
     *@param string $name the name of the parameter
     */
    public function param_exists($name) {
        return array_key_exists($name,$this->_params);
    }
    public function __toString() {
        try {
            return $this->get_url();
        }
        catch(Exception $e ) {
            die($e->__toString());
        }
    }
}
?>