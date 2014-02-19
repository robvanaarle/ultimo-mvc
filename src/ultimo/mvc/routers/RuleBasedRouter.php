<?php

namespace ultimo\mvc\routers;

class RuleBasedRouter implements \ultimo\mvc\Router {
  /**
   * The routing rules indexed by name, a hashtable with rule names as key and
   * Rule objects as value.
   * @var array
   */
  protected $indexedRules = array();
  
  /**
   * The prioritized routing rules, an array of Rule objects.
   * @var array
   */
  protected $prioritizedRuleNames = array();
  
  /**
   * Adds a routing rule. The latest added rule has the highest priority.
   * @param string $name The name of the routing rule.
   * @param Rule $rule The routing rule.
   * @return RuleBasedRouter This instance for fluid design.
   */
  public function addRule($name, Rule $rule=null) {
    $this->removeRule($name);
    
    $this->indexedRules[$name] = $rule;
    array_unshift($this->prioritizedRuleNames, $name);
    return $this;
  }
  
  /**
   * Removed a routing rule.
   * @param string $name The name of the routing rule.
   * @return RuleBasedRouter This instance for fluid design.
   */
  public function removeRule($name) {
    if (isset($this->indexedRules[$name])) {
      // find and remove rule from prioritizedRules if name already exists
      foreach ($this->prioritizedRuleNames as $prio => $prName) {
        if (strcmp($name, $prName) == 0) {
          array_splice($this->prioritizedRuleNames, $prio, 1);
          break;
        }
      }
      unset($this->indexedRules[$name]);
    }
    return $this;
  }
  
  /**
   * Routes a request by adding the module, controller, action and params to the
   * request.
   * @param Application $application The application associated with the
   * routing.
   * @param \ultimo\mvc\Request The request to route.
   * @return \ultimo\mvc\Request The routed request.
   */
  public function route(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) {
    // find the first matching rule
    foreach ($this->prioritizedRuleNames as $name) {
      $rule = $this->indexedRules[$name];
      if ($rule->matches($application, $request)) {
        // let the first matching rule do the unrouting
        return $rule->route($application, $request);
      }
    }
    
    return $request;
   }

  /**
   * Unroutes a request by adding the url to the request.
   * @param Application $application The application associated with the
   * routing.
   * @param \ultimo\mvc\Request $request The request to unroute.
   * @param mixed $ruleName The name of the rule to use for unrouting.
   * @return \ultimo\mvc\Request The unrouted request.
   */
  public function unroute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request, $ruleName = null) {
    if (!isset($this->indexedRules[$ruleName])) {
      return $request;
      //throw new RuleBasedRouterException("Route {$ruleName} does not exist.", RuleBasedRouterException::UNKNOWN_ROUTE_NAME);
    }
    
    return $this->indexedRules[$ruleName]->unroute($application, $request);
  }
}