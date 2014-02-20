<?php

namespace ultimo\mvc;

class Application {
  
  /**
   * The root directory of the application.
   * @var string
   */
  protected $applicationDir;
  
  /**
   * The name of the application.
   * @var string
   */
  protected $name;
  
  /**
   * Cached instances of modules.
   * @var array
   */
  protected $modules = array();
  
  /**
   * The router for converting urls to Request objects.
   * @var Router
   */
  protected $router = null;
  
  /**
   * The request being dispatched.
   * @var Request
   */
  protected $request = null;
  
  /**
   * The response being composed.
   * @var Response
   */
  protected $response = null;
  
  /**
   * The name of the general module.
   * @var string
   */
  protected $generalModuleName = 'general';
  
  /**
   * The plugin broker for application plugins.
   * @var plugins\PluginBroker
   */
  protected $pluginBroker;
  
  /**
   * The environment the application is running in.
   * @var string
   */
  protected $environment = null;
  
  /**
   * The application registry.
   * @var arrayd
   */
  protected $registry = array();
  
  /**
   * Server API.
   * @var \ultimo\net\http\php\sapi\Sapi
   */
  protected $sapi;
  
  /**
   * Constructor.
   * @param string $name The name of the application.
   * @param string $applicationDir The root directory of the application.
   */
  public function __construct($name, $applicationDir) {
    $this->name = $name;
    $this->applicationDir = rtrim($applicationDir, '\\/');
    
    $this->sapi = new \ultimo\net\http\php\sapi\Sapi();
    
    $this->router = new routers\RuleBasedRouter();
    $this->router->addRule('default', new \ultimo\mvc\routers\rules\DynamicRule(':module/:controller/:action', array(
        'module' => 'general',
        'controller' => 'index',
        'action' => 'index'
    )));
    
    // add default plugins
    $this->pluginBroker = new plugins\PluginBroker();
    $this->addPlugin(new plugins\Redirector(), 'redirector');
    $this->addPlugin(new plugins\ViewRenderer(), 'viewRenderer');
    $this->addPlugin(new plugins\ControllerHelpers());
    $this->addPlugin(new plugins\ModuleHelpers());
  }
  
  /**
   * Runs the bootstrap, if present.
   * @return Application This instance for fluid design.
   */
  public function runBootstrap() {
    if (file_exists($this->applicationDir . '/' . 'Bootstrap.php')) {
      require_once($this->applicationDir . '/' . 'Bootstrap.php');
      if (class_exists('Bootstrap')) {
        $bootstrap = new \Bootstrap($this);
        
        if (!$bootstrap instanceof Bootstrap) {
          throw new exceptions\ApplicationException('The bootstrap is not an instance of \ultimo\mvc\Bootstrap', exceptions\ApplicationException::INVALID_BOOTSTRAP_TYPE);
        }
        
        $bootstrap->run();
      }
    }
    
    return $this;
  }
  
  /**
   * Returns the Server API.
   * @return \ultimo\net\http\php\sapi\Sapi Server API.
   */
  public function getSapi() {
    return $this->sapi;
  }
  
  /**
   * Flushes the response to the SAPI. This only works during dispatch.
   * @return Application This instance for fluid design.
   */
  public function flush() {
    if ($this->response === null) {
      throw new exceptions\ApplicationException("Flush only works during dispatch", exceptions\ApplicationException::NOT_DISPATCHING);
    }
    $this->sapi->flush($this->response);
    return $this;
  }
  
  /**
   * Returns the name of the application.
   * @return string The name of the application.
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Returns the root directory of the application.
   * @return string The root directory of the application.
   */
  public function getApplicationDir() {
    return $this->applicationDir;
  }
  
  /**
   * Returns the general module.
   * @return Module The general module.
   */
  public function getGeneralModule() {
    return $this->getModule('modules\\' . $this->getGeneralModuleName());
  }
  
  /**
   * Returns the module with the specified namespace.
   * @param string $namespace The namespace of the module to get.
   * @return Module The module with the specified namespace, or null if no 
   * module with that name exists in the module directories.
   */
  public function getModule($namespace) {
    
    // append default module namespace, if none specified
    if (strpos($namespace, '\\') === false) {
      $namespace = 'modules\\' . $namespace;
    }
    
    // check if the module for this namespace is cached
    if (!isset($this->modules[$namespace])) {
      
      // try to create the module and put it in cache
      $moduleClassName = $namespace . '\Module';
      
      if (class_exists($moduleClassName) && is_subclass_of($moduleClassName, 'ultimo\mvc\Module')) {
        $module = new $moduleClassName($this);
        $this->modules[$namespace] = $module;
        $this->pluginBroker->invoke('onModuleCreated', array($module));
      } else {
        $this->modules[$namespace] = null;
      }
    }
    
    return $this->modules[$namespace];
  }
  
  /**
   * Returns the router for converting urls to Request objects.
   * @return Router The router for converting urls to Request objects.
   */
  public function getRouter() {
    return $this->router;
  }
  
  /**
   * Sets the router for converting urls to Request objects.
   * @param Router $router The router for converting urls to Request objects.
   * @return Application This instance for fluid design.
   */
  public function setRouter(Router $router) {
    $this->router = $router;
    return $this;
  }
  
  /**
   * Sets the name of the general module.
   * @param string $name The name of the general module.
   * @return Application This instance for fluid design.
   */
  public function setGeneralModuleName($name) {
    $this->generalModuleName = $name;
    return $this;
  }
  
  /**
   * Returns the name of the general module.
   * @return string The name of the index module.
   */
  public function getGeneralModuleName() {
    return $this->generalModuleName;
  }
  
  /**
   * Returns the request being dispatched.
   * @return Request The request being dispatched.
   */
  public function getRequest() {
    return $this->request;
  }
  
  /**
   * Returns the response being composed.
   * @return Response The reponse being composed.
   */
  public function getResponse() {
    return $this->response;
  }
  
  /**
   * Runs the application.
   * @param Request $request The request to run, or null to use the current
   * request from the SAPI.
   * @param bool $flushResponse Whether to flush the response after run.
   * @return Response Response to the request.
   */
  public function run(Request $request = null, $flushResponse = true) {
    // get the request from the SAPI?
    if ($request === null) {
      $request = $this->sapi->getRequest(new Request());
    }
    
    // call 'onRoute' on all application plugins
    $this->pluginBroker->invoke('onRoute', array($this, $request));
    
    // get the Request object from the router
    $router = $this->getRouter();
    $request = $router->route($this, $request);
    
    // call 'onRouted' on all application plugins
    $this->pluginBroker->invoke('onRouted', array($this, $request));


    // dispatch the request
    $response = $this->dispatch($request);
    
    // flush the response using the SAPI
    if ($flushResponse) {
      $this->getSapi()->flush($response);
    }
    
    return $response;
  }
  
  /**
   * Dispatched a request.
   * @param Request $request The request to dispatch.
   * @return Response Reponse to the request.
   */
  public function dispatch(Request $request=null) {
    // create a response to compose, and store the request
    $this->response = new Response();
    $this->request = $request;
    
    // start the dispatch loop
    do {
      if ($request === null) {
        throw new exceptions\DispatchException("No request specified.", exceptions\DispatchException::INVALID_REQUEST);
      }
      
      // plugins can alter the request and let it redispatch, make sure the
      // request at the beginning of this dispatch loop is reset
      $request->setRedispatch(false);
      
      // call 'onDispatch' on all application plugins
      $this->pluginBroker->invoke('onDispatch', array($this));
      
      // if the request was marked for redispatch, restart the dispatch loop
      if ($request->getRedispatch()) {
        continue;
      }
      
      try {
        $moduleNamespace = $request->getModule();
        
        if ($moduleNamespace === null) {
          throw new exceptions\DispatchException("Page not found.", exceptions\DispatchException::PAGE_NOT_FOUND);
        }
        
        $module = $this->getModule($moduleNamespace);

        if ($module === null) {
          throw new exceptions\DispatchException("Module '{$moduleNamespace}' not found.", exceptions\DispatchException::PAGE_NOT_FOUND);
        }
        
        // abstract and partial modules may not be dispatched
        if ($module->isAbstract()) {
          throw new exceptions\DispatchException("Cannot dispatch on abstract module '{$moduleNamespace}'.", exceptions\DispatchException::ABSTRACT_MODULE);
        }
        
        if ($module->isPartial()) {
          throw new exceptions\DispatchException("Cannot dispatch on partial module '{$moduleNamespace}'.", exceptions\DispatchException::PARTIAL_MODULE);
        }
        
        // get the name of the requested controller
        $controllerName = $request->getController();
        if ($controllerName === null) {
          $controllerName = $module->getIndexControllerName();
        }
        
        // get the requested controller
        $controller = $module->getController($controllerName);
        if ($controller === null) {
          throw new exceptions\DispatchException("Controller '{$controllerName}' in module '{$moduleNamespace}' not found.", exceptions\DispatchException::PAGE_NOT_FOUND);
        }
        
        // get the name of the requested action
        $actionName = $request->getAction();
        if ($actionName === null) {
          $actionName = $controller->getIndexActionName();
        }
        if (!$controller->isAction($actionName)) {
          throw new exceptions\DispatchException("Action '{$actionName}' in controller '{$controllerName}' in module '{$moduleNamespace}' not found.", exceptions\DispatchException::PAGE_NOT_FOUND);
        }
    
        // call the action
        $controller->call($actionName);
        
      } catch (\Exception $e) {
        $this->response->addException($e);
      }
      
      // if the request was marked for redispatch, restart the dispatch loop
      if ($request->getRedispatch()) {
        continue;
      }
      
      // call 'onDispatched' on all application plugins
      $this->pluginBroker->invoke('onDispatched', array($this));
      
    } while($request->getRedispatch());

    $result = $this->response;
    
    // clean up
    $this->request = null;
    $this->response = null;
    
    return $result;
  }
  
  /**
   * Adds an application plugin, optionally with a name.
   * @param plugins\ApplicationPlugin $plugin The application plugin to add.
   * @param string $name The name with which the plugin can retrieved.
   * @return Applications This instance for fluid design.
   */
  public function addPlugin(plugins\ApplicationPlugin $plugin, $name=null) {
    $this->pluginBroker->addPlugin($plugin, $name);
    $plugin->onPluginAdded($this);
    return $this;
  }
  
  /**
   * Returns the plugin with the specified name.
   * @param string $name The name of the plugin to get.
   * @return plugins\ApplicationPlugin The plugin with the specified name, or
   * null if no plugin with that name exists.
   */
  public function getPlugin($name) {
    return $this->pluginBroker->getPlugin($name);
  }
  
  /**
   * Sets the environment the application in running in.
   * @param string $environment The environment the application in running in.
   * @return Application This instance for fluid design.
   */
  public function setEnvironment($environment) {
    $this->environment = $environment;
    return $this;
  }
  
  /**
   * Returns the environment the application is running in.
   * @return string The environment the application is running in.
   */
  public function getEnvironment() {
    return $this->environment;
  }
  
  /**
   * Stores a value on a key in the application registry.
   * @param string $key The key to store the value on.
   * @param mixed $value The value to store on the key.
   * @return Application This instance for fluid design.
   */
  public function setRegistry($key, $value) {
    $this->registry[$key] = $value;
    return $this;
  }
  
  /**
   * Returns the value in the registry stored on a key.
   * @param string $key The key of the value to get.
   * @param mixed $fallbackValue The value to return if the key is not present
   * in the registry.
   * @return mixed The value in the registry stored on the key, or the
   * fallback value if the key is not present in the registry.
   */
  public function getRegistry($key, $fallbackValue=null) {
    if (!array_key_exists($key, $this->registry)) {
      return $fallbackValue;
    }
    
    return $this->registry[$key];
  }
}