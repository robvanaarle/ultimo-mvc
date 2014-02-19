<?php

namespace ultimo\mvc\plugins;

class ErrorHandler implements ApplicationPlugin {
  
  protected $errorHandlingActive = false;
  
  /**
   *
   * @var \ultimo\debug\error\ErrorHandler
   */
  protected $debugErrorHandler = null;
  
  public function setDebugErrorHandler(\ultimo\debug\error\ErrorHandler $debugErrorHandler) {
    $this->debugErrorHandler = $debugErrorHandler;
  }
  
  public function onPluginAdded(\ultimo\mvc\Application $application) { }
  
  public function onModuleCreated(\ultimo\mvc\Module $module) { }
  
  public function onRoute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) { }
  
  public function onRouted(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request=null) { }
  
  public function onDispatch(\ultimo\mvc\Application $application) { }
  
  /**
   * After dispatch, this function checks whether the response contains an
   * exception. If so, it forwards the request to the error controller in the
   * index module.
   */
  public function onDispatched(\ultimo\mvc\Application $application) {
    
    if (!$this->errorHandlingActive) {
      $response = $application->getResponse();
      $request = $application->getRequest();
      $exceptions = $response->getExceptions();
      $response->clearExceptions();
      $this->errorHandlingActive = true;
      
      if (!empty($exceptions)) {
        
        if ($this->debugErrorHandler !== null) {
          $exception = $exceptions[0];
          if (!$exception instanceof \ultimo\mvc\exceptions\DispatchException || $exception->getCode() !== \ultimo\mvc\exceptions\DispatchException::PAGE_NOT_FOUND) {
            $this->debugErrorHandler->handleCaughtException($exception);
          }
        }
        
        $originalRequest = clone $request;
        $request->setAction('error')
                ->setController('error')
                ->setModule($application->getGeneralModuleName())
                ->setPostParam('exceptions', $exceptions)
                ->setPostParam('request', $originalRequest)
                ->setRedispatch(true);
      }
    } else {
      $response = $application->getResponse();
      $exceptions = $response->getExceptions();
      if (!empty($exceptions)) {
        throw $exceptions[0];
      }
    }
  }
}