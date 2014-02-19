<?php

namespace ultimo\mvc\routers\rules;

class BasicQueryStringRule implements \ultimo\mvc\routers\Rule {
  
  /**
   * The path to match against.
   * @var string
   */
  protected $path;
  
  /**
   * The parameters, an hashtable with parameter name as key and parameter
   * value as value.
   * @var array
   */
  protected $defaultParams = array();
  
  public function __construct($path,array $defaultParams=array()) {
    $path = trim($path, '/');
    if ($path != '') {
      $path = '/' . $path;
    }
    $this->path = $path;
    
    $this->defaultParams = $defaultParams;
  }
  
  public function matches(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    $path = rtrim($request->getRelevantUrlPath(), '/') . '/';
    $matchPath = $this->path . '/';
    
    return ($path == $matchPath);
  }
  
  public function route(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    
    // add missing parameters from default params
    $params = $request->getParams();
    foreach ($this->defaultParams as $name => $value) {
      if (!isset($params[$name])) {
        $request->setGetParam($name, $value);
      }
    }
    
    $params = $request->getParams();
    
    // fetch module, controller and action params and set them as values to
    // dispatch, let the parameters be leading
    if (isset($params['module'])) {
      $request->setModule($params['module']);
    }
    
    if (isset($params['controller'])) {
      $request->setController($params['controller']);
    }
    
    if (isset($params['action'])) {
      $request->setAction($params['action']);
    }
    
    return $request;
  }
  
  public function unroute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    $getParams = array_merge($this->defaultParams, $request->getParams());
    
    $getParams['module'] = $request->getModule();
    if (isset($this->defaultParams['module']) && $getParams['module'] == $this->defaultParams['module']) {
      unset($getParams['module']);
    }
    
    $getParams['controller'] = $request->getController();
    if (isset($this->defaultParams['controller']) && $getParams['controller'] == $this->defaultParams['controller']) {
      unset($getParams['controller']);
    }
    
    $getParams['action'] = $request->getAction();
    if (isset($this->defaultParams['action']) && $getParams['action'] == $this->defaultParams['action']) {
      unset($getParams['action']);
    }
    
    
    // set newly constructed get params
    $request->clearGetParams();
    $request->setGetParams($getParams);
    
    $request->setUrl($request->getBaseUrl() . $this->path);
    return $request;
  }
}