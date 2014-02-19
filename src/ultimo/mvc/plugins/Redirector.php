<?php

namespace ultimo\mvc\plugins;

class Redirector implements ApplicationPlugin, ModulePlugin, ControllerPlugin {
  
  /**
   * The application the redirector is for.
   * @var \ultimo\mvc\Application
   */
  protected $application;
  
  /**
   * The url to redirecto to. Null if no redirection is desired.
   * @var string
   */
  protected $redirectUrl = null;
  
  /**
   * HTTP status code (3xx).
   * @var int
   */
  protected $statusCode = 302;
  
  public function onPluginAdded(\ultimo\mvc\Application $application) {
    $this->application = $application;
  }
  
  /**
   * Attaches itself to the created module.
   */
  public function onModuleCreated(\ultimo\mvc\Module $module) {
    $module->addPlugin($this);
  }
  
  public function onRoute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) { }
  
  public function onRouted(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request=null) { }
  
  public function onDispatch(\ultimo\mvc\Application $application) { }
  
  public function onDispatched(\ultimo\mvc\Application $application) {
    if ($application->getRequest()->getRedispatch()) {
      return;
    }
    
    if ($this->redirectUrl !== null) {
      $response = $application->getResponse();
      $response->redirect($this->redirectUrl, $this->statusCode);
      $response->clearBody();
    }
  }
  
  /**
   * Attaches itself to the created controller.
   */
  public function onControllerCreated(\ultimo\mvc\Controller $controller) {
    $controller->addPlugin($this, 'redirector');
  }
  
  public function onActionCall(\ultimo\mvc\Controller $controller, &$actionName) { }
  
  /**
   * Check whether a redirect is needed, if so, execute it.
   */
  public function onActionCalled(\ultimo\mvc\Controller $controller, $actionName) { }
  
  /**
   * Sets the url to redirect to.
   * @param $url The url to redirect to.
   */
  public function setRedirectUrl($url) {
    if (strpos($url, '://') === false && $url !== null) {
      if ($url !== '') {
        $url = '/' . ltrim('/', $url);
      }
      $url = $this->application->getRequest()->getBaseUrl() . $url;
    }
    
    $this->redirectUrl = $url;
    $viewRenderer = $this->application->getPlugin('viewRenderer');
    if ($viewRenderer !== null) {
      $viewRenderer->setDisabled($url !== null);
    }
    
    return $this;
  }
  
  /**
   * Sets the HTTP status code.
   * @param int $statusCode HTTP status code.
   * @return \ultimo\mvc\plugins\Redirector This instance for fluid design.
   */
  public function statusCode($statusCode) {
    $this->statusCode = $statusCode;
    return $this;
  }
  
  /**
   * Sets the url parameters to redirect to.
   * @param array $params the url parameters to redirect to.
   * @param boolean $resetUserParams Whether to not to use the parameters from
   * the request being dispatched.
   * @return \ultimo\mvc\plugins\Redirector This instance for fluid design.
   */
  public function redirect(array $params = array(), $metadata = null, $resetUserParams=true) {
    // construct request to unroute
    $request = new \ultimo\mvc\Request();
    $request->setBasePath($this->application->getRequest()->getBasePath());
    $request->setUrl($this->application->getRequest()->getUrl(false));
    
    $currentRequest = $this->application->getRequest();
    
    if ($resetUserParams) {
      $defaultGetParams = array();
    } else {
      $defaultGetParams = $currentRequest->getGetParams();
    }
    
    // module, controller and action could be part of params list. Use those
    // from current request as default
    $defaultGetParams['module'] = $currentRequest->getModule();
    $defaultGetParams['controller'] = $currentRequest->getController();
    $defaultGetParams['action'] = $currentRequest->getAction();
    
    $getParams = array_merge($defaultGetParams, $params);
    
    
    $request->setModule($getParams['module']);
    unset($getParams['module']);
    
    $request->setController($getParams['controller']);
    unset($getParams['controller']);
    
    $request->setAction($getParams['action']);
    unset($getParams['action']);
    
    // add cleaned get Params
    $request->clearGetParams();
    $request->setGetParams($getParams);
    

    $router = $this->application->getRouter();
    
    if ($router instanceof \ultimo\mvc\routers\RuleBasedRouter && $metadata === null) {
      $metadata = 'default';
    }
    
    // unroute and return the resulting url
    $request = $router->unroute($this->application, $request, $metadata);
    $url = $request->getUrl();
    
    $this->setRedirectUrl($url);
    return $this;
  }
  
}