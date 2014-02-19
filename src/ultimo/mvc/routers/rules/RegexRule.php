<?php

namespace ultimo\mvc\routers\rules;

/**
 * Example:
 * $router->addRule('info.infoitem.test2', new \ultimo\mvc\routers\rules\RegexRule(
 *      'algemene-informatie\/(.+)_(\d+)_(\d+)',
 *      'algemene-informatie/%s_%d_%d',
 *       array(
 *        'module' => 'info',
 *        'controller' => 'infoitem',
 *        'action' => 'read'
 *       ),
 *       array(
 *         'title', 'id', 'id'
 *       )
 *  ));
 */
class RegexRule implements \ultimo\mvc\routers\Rule {

  protected $pattern;
  
  protected $format;
  
  /**
   * The parameters, an hashtable with parameter name as key and parameter
   * value as value.
   * @var array
   */
  protected $params = array();
  
  protected $paramNames = array();
  
  public function __construct($pattern, $format, array $params=array(), array $paramNames=array()) {
    $this->pattern = '\/' . trim($pattern, '/');
    $this->format = '/' . trim($format, '/');
    $this->params = $params;
    $this->paramNames = $paramNames;
  }
  
  public function matches(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    return preg_match('/^' . $this->pattern . '$/', $request->getRelevantUrlPath());
  }
  
  public function route(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    preg_match('/^' . $this->pattern . '$/', $request->getRelevantUrlPath(), $matches);
    
    // remove entire path from matches
    array_shift($matches);
    
    // start with default values of params
    $params = $this->params;
    
    // add/overwrite with matches params
    foreach ($matches as $index => $match) {
      $params[$this->paramNames[$index]] = $match;
    }
    
    // add/overwrite with get params
    $params = array_merge($params, $request->getParams());
    
    // set m/c/a
    if (isset($params['module'])) {
      $request->setModule($params['module']);
    }
    
    if (isset($params['controller'])) {
      $request->setController($params['controller']);
    }
    
    if (isset($params['action'])) {
      $request->setAction($params['action']);
    }
    
    // add all params as get params
    $request->setGetParams($params);
    
    return $request;
  }
  
  public function unroute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
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
    
    $vsprintfArgs = array();
    $getParamsNamesToRemove = array();
    foreach ($this->paramNames as $paramName) {
      $value = '';
      if (isset($params[$paramName])) {
        $value = $params[$paramName];
      }
      
      
      $vsprintfArgs[] = $value;
      
      // remove get param later, as it might be needed in the next iteration
      $getParamsNamesToRemove[] = $paramName;
    }
    
    // remove unnecessary get params
    foreach ($getParamsNamesToRemove as $paramName) {
      unset($getParams[$paramName]);
    }
    
    // build relevant path
    $relevantPath = vsprintf($this->format, $vsprintfArgs);
    
    // set newly constructed get params
    $request->clearGetParams();
    $request->setGetParams($getParams);
    
    $request->setUrl($request->getBaseUrl() . $relevantPath);
    return $request;
  }
}