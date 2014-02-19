<?php

namespace ultimo\mvc\routers\rules;

class StaticRule implements \ultimo\mvc\routers\Rule {
  
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
  protected $params = array();
  
  public function __construct($path,array $params=array()) {
    $path = trim($path, '/');
    if ($path != '') {
      $path = '/' . $path;
    }
    $this->path = $path;
    
    $this->params = $params;
  }
  
  public function matches(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    $path = rtrim($request->getRelevantUrlPath(), '/') . '/';
    $matchPath = $this->path . '/';
    
    return ($path == $matchPath);
  }
  
  public function route(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    // add local params as get params
    $request->setGetParams($this->params);
    
    $params = $request->getParams();
    
    // fetch module, controller and action params and set them as values to
    // dispatch
    if (isset($this->params['module'])) {
      $request->setModule($this->params['module']);
    } elseif (isset($params['module'])) {
      $request->setModule($params['module']);
    }
    
    if (isset($this->params['controller'])) {
      $request->setController($this->params['controller']);
    } elseif (isset($params['controller'])) {
      $request->setController($params['controller']);
    }
    
    if (isset($this->params['action'])) {
      $request->setAction($this->params['action']);
    } elseif (isset($params['action'])) {
      $request->setAction($params['action']);
    }
    
    return $request;
  }
  
  public function unroute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    $getParams = $request->getGetParams();
    
    // if module, controller or action is not part of this static rule, use
    // take those from request to unroute and add them ass get parameters
    if (!isset($this->params['module'])) {
      $getParams['module'] = $request->getModule();
    }
    
    if (!isset($this->params['controller'])) {
      $getParams['controller'] = $request->getController();
    }
    
    if (!isset($this->params['action'])) {
      $getParams['action'] = $request->getAction();
    }
    
    // set newly constructed get params
    $request->clearGetParams();
    $request->setGetParams($getParams);
    
    $request->setUrl($request->getBaseUrl() . $this->path);
    return $request;
  }
}