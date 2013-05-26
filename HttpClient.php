<?php 

class Http {
  protected $_opt = array(
        CURLOPT_USERAGENT=>'LiteSolr (cUrl)',
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER=>array('Expect: '),
        CURLOPT_FRESH_CONNECT => 0,
        CURLOPT_FORBID_REUSE => 0,
        CURLOPT_BINARYTRANSFER => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 600,
  );
  protected $_curl=null;
  
  public function __construct($options=array()) {
	   $this->_curl = curl_init();
	   $this->_opt = $options + $this->_opt;
  }

  public function __destruct()
  {
    if(gettype($this->_curl) == 'resource') curl_close($this->_curl);
  }

/**
 * Send a POST requst using cURL
 * @param string $url to request
 * @param array $params values to send
 * @param array $options for cURL
 * @return info array, result string
 */
public function post($url, $params = array(), $options = array())
{
    $options += array(
        CURLOPT_POST => 1,
        CURLOPT_URL => $url,
        CURLOPT_POSTFIELDS => (is_string($params))?$params:http_build_query($params),
    ) + $this->_opt;
    //curl_reset($this->_curl);
    curl_setopt_array($this->_curl, $options);
    if( ! $result = curl_exec($this->_curl))
    {
        return array(array('code'=>'', 'Content-Type'=>'', 'curl-Error'=>curl_error($ch)), '');
    }
    $info=array(
      'code'=>curl_getinfo ($this->_curl, CURLINFO_HTTP_CODE),
      'Content-Type'=>curl_getinfo ($this->_curl, CURLINFO_CONTENT_TYPE)
    );
    //curl_close($this->_curl);
    return array($info, $result);
}

/**
 * Send a GET requst using cURL
 * @param string $url to request
 * @param array $params values to send
 * @param array $options for cURL
 * @return string
 */
public function get($url, array $params = array(), array $options = array())
{   
    $options += array(
        CURLOPT_URL => $url. (($params)?((strpos($url, '?') === FALSE ? '?' : ''). http_build_query($params)):''),
    ) + $this->_opt;
    curl_setopt_array($this->_curl, $options);
    if( ! $result = curl_exec($this->_curl))
    {
        return array(array('code'=>'', 'Content-Type'=>'', 'curl-Error'=>curl_error($this->_curl)), '');
    }
    $info=array(
      'code'=>curl_getinfo ($this->_curl, CURLINFO_HTTP_CODE),
      'Content-Type'=>curl_getinfo ($this->_curl, CURLINFO_CONTENT_TYPE)
    );
    return array($info, $result);
}


    /**
     * call unusual method like PUT, DELETE, PURGE ..etc.
     * */
    public function __call($method,$args){
		$params=shift($args);
		$options=shift($args);
		$options[CURLOPT_CUSTOMREQUEST]=strtoupper($method);
		return $this->post($url, $params, $options);
    }

}


 
