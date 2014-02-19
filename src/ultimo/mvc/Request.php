<?php

namespace ultimo\mvc;

class Request extends \ultimo\net\http\XRequest {  
  /**
   * The base path, i.e. /~myaccount/shop
   * @var string
   */
  protected $basePath = '';
  
  /**
   * The module name.
   * @var string
   */
  protected $module;
  
  /**
   * The controller name.
   * @var string
   */
  protected $controller;
  
  /**
   * The action name.
   * @var string
   */
  protected $action;
   
  /**
   * Whether the request is marked to be redispatched.
   * @var boolean
   */
  protected $redispatch = false;
  
  /**
   * Returns the relevant url path: the url path without the base path.
   * @return string The relevant url path.
   */
  public function getRelevantUrlPath($decode=true) {
    $urlPath = $this->getUri(false);
    
    if ($decode) {
      $urlPath = urldecode($urlPath);
    }
    
    $basePath = $this->getBasePath();
    if ($basePath == '/') {
      return $urlPath;
    }
    
    // if base path is /test, and url is /testme/, adding traling slashes
    // will prevent this from being removed erroneously
    $fixedUrlPath = rtrim($urlPath, '/') . '/';
    $fixedBasePath = rtrim($basePath, '/') . '/';
    
    
    if (strpos($fixedUrlPath, $fixedBasePath) === 0) {
      $urlPath = substr($urlPath, strlen($basePath));
    }

    return $urlPath;
  }
  
  /**
   * Sets the base path. If the request has  http://server.com/accounts/foobar
   * as base then 'accounts/foobar' use as the base path.
   * @param string $basePath The base path.
   * @return Application This instance for fluid design.
   */
  public function setBasePath($basePath) {
    if ($basePath != '') {
      $basePath = '/' . trim($basePath, '/');
    }
    $this->basePath = $basePath;
    return $this;
  }
  
  /**
   * Returns the base path.
   * @return string The base path.
   */
  public function getBasePath() {
    return $this->basePath;
  }
  
  public function getBaseUrl() {
    $hostHeader = $this->getHeader('Host');
    return $this->getScheme() . '://' . $hostHeader->getHeaderValue() . $this->getBasePath();
  }
  
  /**
   * Returns the module name.
   * @return string The module name.
   */
  public function getModule() {
    return $this->module;
  }
  
  /**
   * Sets the module name.
   * @param string $module The module name..
   * @return Request This instance for fluid design.
   */
  public function setModule($module) {
    $this->module = $module;
    return $this;
  }
  
  /**
   * Returns the controller name.
   * @return string The controller name.
   */
  public function getController() {
    return $this->controller;
  }
  
  /**
   * Sets the controller name.
   * @param string $controller The controller name.
   * @return Request This instance for fluid design.
   */
  public function setController($controller) {
    $this->controller = $controller;
    return $this;
  }
  
  /**
   * Returns the action name.
   * @return string The action name.
   */
  public function getAction() {
    return $this->action;
  }
  
  /**
   * Sets the action name.
   * @param string $action The actin name.
   * @return Request This instance for fluid design.
   */
  public function setAction($action) {
    $this->action = $action;
    return $this;
  }
  
  /**
   * Returns the parameters.
   * @return array The parameters.
   */
  public function getParams() {
    return array_merge($this->getGetParams(), $this->getPostParams());
  }
  
  /**
   * Returns the value of the parameter with the specified name.
   * @param string $name The name of the parameter value to get.
   * @param string|array $fallbackValue The value to returns if the parameter 
   * with the specified name does not exist.
   * @return string|array The value of the parameter with the specified name, or 
   * the fallback value if the parameter with the specified name does not exist.
   */
  public function getParam($name, $fallbackValue=null) {
    $value = $this->getPostParam($name);
    
    if ($value === null) {
      $value = $this->getGetParam($name);
    }
    
    if ($value === null) {
      $value = $fallbackValue;
    }
    
    return $value;
  }
  
  /**
   * Returns whether the request was issued using an XMLHttpRequest.
   * @return boolean Whether the request was issued using an XMLHttpRequest.
   */
  public function isXMLHttpRequest() {
    $header = $this->getHeader('X-Requested-With');
    return ($header !== null && $header->getHeaderValue() == 'XMLHttpRequest');
  }
  
  /**
   * Returns whether the request was issued using ajax.
   * @return boolean Whether the request was issued using ajax.
   */
  public function isAjax() {
    return $this->isXMLHttpRequest();
  }
  
  
  
  /**
   * Returns the value of the header with the specified name.
   * @param string $name The name of the header value to get.
   * @param string|array $fallbackValue The value to returns if the header with
   * the specified name does not exist.
   * @return string The value of the header with the specified name, or the
   * fallback value if the header with the specified name does not exist.
   */
  public function getHeaderValue($name, $fallbackValue=null) {
    $header = $this->getHeader($name);
    if ($header === null) {
      return $fallbackValue;
    }
    return $header->getHeaderValue();
  }
  
  /**
   * Marks the request for redispatch.
   * @param boolean $redispatch Whether to mark the request for redispatch.
   * @return Request This instance for fluid design.
   */
  public function setRedispatch($redispatch) {
    $this->redispatch = $redispatch;
    return $this;
  }
  
  /**
   * Returns whether the request is marked for redispatch.
   * @return boolean Whether the requesst is marked for redispatch.
   */
  public function getRedispatch() {
    return $this->redispatch;
  }
  
  /**
   * Returns whether the http method is POST.
   * @return boolean Whether the http method is POST.
   */
  public function isPost() {
    return $this->method == self::METHOD_POST;
  }
}