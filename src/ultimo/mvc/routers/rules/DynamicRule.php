<?php

namespace ultimo\mvc\routers\rules;

/**
 * Example:
 * $router->addRule('info.infoitem.read', new \ultimo\mvc\routers\rules\DynamicRule('algemene-informatie/:id', array(
 *      'module' => 'info',
 *      'controller' => 'infoitem',
 *      'action' => 'read'
 *  )));
 */
class DynamicRule implements \ultimo\mvc\routers\Rule {
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
  
  public function __construct($path, array $params=array()) {
    $this->path = '/' . trim($path, '/');
    $this->params = $params;
  }
  
  protected function extractParamsFromPath(\ultimo\mvc\Request $request) {
    $params = $this->params;
    
    $requestPathElems = explode('/', trim($request->getRelevantUrlPath(), '/'));
    $matchPathElems = explode('/', trim($this->path, '/'));
    
    if ($requestPathElems[0] == '') {
      $requestPathElems = array();
    }
    
    if (count($requestPathElems) > count($matchPathElems)) {
      return null;
    }
    
    foreach ($matchPathElems as $index => $matchPathElem) {
      // find the corresponding request path element
      $requestPathElem = null;
      if (isset($requestPathElems[$index])) {
        $requestPathElem = $requestPathElems[$index];
      }
      
      if ($matchPathElem[0] != ':') {
        // the match path element is a hard value, the corresponding request
        // path element must be of equal value
        if ($matchPathElem != $requestPathElem) {
          return null;
        }
      } else {
        $paramName = substr($matchPathElem, 1);
        // the match path element is a variable, the corresponding request path
        // element must be present or have a default value assigned to
        if ($requestPathElem === null) {
          if (!isset($this->params[$paramName])) {
            return null;
          }
        } else {
          $params[$paramName] = $requestPathElem;
        }
      }
    }
    
    return $params;
  }
  
  public function matches(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    return $this->extractParamsFromPath($request) !== null;
  }
  
  public function route(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    $params = $this->extractParamsFromPath($request);
    
    // append get, post and files parameters
    $params = array_merge($params, $request->getParams());
    

    if (isset($params['module'])) {
      $request->setModule($params['module']);
    }
    
    if (isset($params['controller'])) {
      $request->setController($params['controller']);
    }
    
    if (isset($params['action'])) {
      $request->setAction($params['action']);
    }
    
    // add params as get params
    $request->setGetParams($params);
    
    return $request;
  }
  
  public function unroute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    $relevantPath = $this->path;
    
    $params = array_merge($this->params, $request->getParams());
    
    $getParams = $request->getParams();
    
    $specialParams = array(
        'module' => $request->getModule(),
        'controller' => $request->getController(),
        'action' => $request->getAction()
    );
    
    foreach ($specialParams as $name => $value) {
      if ($value !== null) {
        $params[$name] = $value;
      }
    }
    
    
    $matchPathElems = explode('/', trim($this->path, '/'));
    $getParamsNamesToRemove = array();
    
    $search = array();
    $replace = array();
    
    foreach ($matchPathElems as $index => $matchPathElem) {
      if ($matchPathElem[0] == ':') {
        $paramName = substr($matchPathElem, 1);
        $paramValue = '';
        if (isset($params[$paramName])) {
          $paramValue = $params[$paramName];
        }
        
        // replace later, to prevent replace of replaced values containing ':xxx'
        $search[] = $matchPathElem;
        $replace[] = urlencode($paramValue);
        
        // remove get param later, as it might be needed in the next iteration
        $getParamsNamesToRemove[] = $paramName;
      }
    }
    
    // remove unnecessary get params 
    foreach ($getParamsNamesToRemove as $paramName) {
      unset($getParams[$paramName]);
    }
    
    // build relevant path
    $relevantPath = str_replace($search, $replace, $relevantPath);
    
    // set newly constructed get params
    $request->clearGetParams();
    $request->setGetParams($getParams);
    
    $request->setUrl($request->getBaseUrl() . $relevantPath);
    return $request;
  }
}