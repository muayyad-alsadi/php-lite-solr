<?php 
require_once(dirname(__FILE__)."/HttpClient.php");

/**
 * LiteSolr a JSON-only solr client
 * 
 **/
class LiteSolr {
  public $prefix;
  protected $_mapping=array(
      'ping'=>array('method'=>'get', 'uri'=>'admin/ping'),
      'update'=>array('method'=>'post', 'uri'=>'update'), // in some versions in uri is update/json
  );
  protected $_http;
  
  public function __construct($prefix='http://localhost:8983/solr/', $mapping=array(), $options=array()) {
      $this->prefix=$prefix;
      $this->_http=new HttpClient($options);
      $this->ping();
  }

  /**
   * commit: a shortcut on update
   * params: can be array('waitSearcher'=>true, 'softCommit'=>false, 'expungeDeletes'=>false)
   * see http://wiki.apache.org/solr/UpdateXmlMessages#A.22commit.22_and_.22optimize.22
   **/
  public function commit($params=null){
    if (!$params) $params=new stdClass();
    var_dump($params);
    return $this->update(array("commit"=>$params));
  }
  
  /**
   * optimize: a shortcut on update
   * @params: can be array('waitSearcher'=>true, 'softCommit'=>false, 'maxSegments'=>1)
   **/
  public function optimize($params=null){
    if (!$params) $params=new stdClass();
    return $this->update(array("optimize"=> $params));
  }

  /**
   * delete: a shortcut on update
   * @params: can be for example array('query'=>'*', 'commitWithin'=>'1000')
   * 
   **/
  public function delete($params){
    return $this->update(array('delete'=>$params));
  }

  public function __call($action, $args){
      if (!isset($this->_mapping[$action])) {
          $m=array();
      } else {
          $m=$this->_mapping[$action];
      }
      $uri=isset($m['uri'])?$m['uri']:str_replace('_', '/', $action);
      $method=isset($m['method'])?$m['method']:((count($args)<=2)?'get':'post');
      if ($method=='get') {
          $params=array_merge(array($uri), $args);
          return call_user_func_array( array($this, '_json_get'), $params);
      } else {
          $params=array_merge(array($method, $uri), $args);
           return call_user_func_array( array($this, '_json_custom_method'), $params);  
      }
  }
  
  public function _json_get($action, $params=array(), $options=array()) {
      // TODO: we might need add 'ts'=>time() to all get params
      // we might also need to add 'indent'=>'true' for debugging
      list($info,$content_s)=$this->_http->get($this->prefix.$action, $params+array('wt'=>'json'), $options);
      if ($info['code']!=200 || null===($content=json_decode($content_s, 1))) {
          var_dump($info);
          var_dump($content_s);
          throw new Exception('bad response');
      }
      return $content;
  }
  
  public function _json_post($action, $params, $get_params=array(), $options=array()) {
      return $this->json_custom_method('post', $action, $params, $get_params, $options);
  }

  public function _json_custom_method($method, $action, $params, $get_params=array(), $options=array()) {
      $options+=array(CURLOPT_HTTPHEADER => array('Content-type: application/json'));
      if (!is_string($params)) $params=json_encode($params);
      $url=$this->prefix.$action;
      $get_params+=array('wt'=>'json');
      $url.=(strpos($url, '?') === FALSE ? '?' : '&').http_build_query($get_params);
      list($info,$content_s)= call_user_func_array( array($this->_http, $method), array($url, $params, $options));
      // TODO: we might need to consider 2xx not just 200
      if ($info['code']!=200 || null===($content=json_decode($content_s, 1))) {
          var_dump($info);
          var_dump($content_s);
          throw new Exception('bad response');
      }
      return $content;
      
  }


}
