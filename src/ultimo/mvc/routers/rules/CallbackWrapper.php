<?php

namespace ultimo\mvc\routers\rules;

/**
 * Example:
 * $router->addRule('info.infoitem.test', new CallbackWrapper(
 *    new DynamicRule('test/:title/:id', array(
 *      'module' => 'info',
 *      'controller' => 'infoitem',
 *      'action' => 'read'
 *    )),
 *    function($a, $r) {
 *      $r->setGetParam('id', $r->getParam('model')->id);
 *      $r->setGetParam('title', Slug::slugify($r->getParam('model')->title));
 *      
 *      $r->setGetParam('model', null);
 *    }
 *  ));
 */
class CallbackWrapper implements \ultimo\mvc\routers\Rule {
  
  /**
   * Wrapped routing rule.
   * @var \ultimo\mvc\routers\Rule
   */
  protected $rule;
  
  /**
   * Callback function called before unrouting.
   * @var callable
   */
  protected $beforeUnroute;
  
  /**
   * Callback function called after routing.
   * @var callable
   */
  protected $afterRoute;
  
  public function __construct(\ultimo\mvc\routers\Rule $rule, $beforeUnroute, $afterRoute=null) {
    $this->rule = $rule;
    $this->beforeUnroute = $beforeUnroute;
    $this->afterRoute = $afterRoute;
  }
  
  public function matches(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    return $this->rule->matches($application, $request);
  }
  
  public function route(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    $request = $this->rule->route($application, $request);
    if ($this->afterRoute === null) {
      return $request;
    }
    
    $f = $this->afterRoute;
    $f($application, $request);
    
    return $request;
  }
  
  public function unroute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    // call the callback
    if ($this->beforeUnroute !== null) {
      $f = $this->beforeUnroute;
      $f($application, $request);
    }
    
    return $this->rule->unroute($application, $request);
  }
}