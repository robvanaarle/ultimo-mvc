<?php

namespace ultimo\mvc;

abstract class Controller extends Facade {
  
  /**
   * The view the controller must use to render.
   * @var \ultimo\mvc\View
   */
  protected $view;
  
  /**
   * The request being dispatched.
   * @var \ultimo\mvc\Request
   */
  protected $request;
  
  /**
   * The response of the request.
   * @var \ultimo\mvc\Response
   */
  protected $response;
  
  /**
   * The name of the called action.
   * @var string
   */
  protected $calledActionName;
  
  /**
   * The plugin broker for controller plugins.
   * @var \ultimo\mvc\plugins\PluginBroker
   */
  protected $pluginBroker;
  
  /**
   * The cached namespace of this controller.
   * @var string
   */
  protected $namespace;
  
  /**
   * The cached name of this controller, without the 'Controller' postfix.
   * @var string
   */
  protected $name;
  
  /**
   * Constructor.
   * @param string $name Name of the controller.
   * @param Module $module The module this controller is constructed for.
   */
  public function __construct($name, Module $module) {
    $this->view = $module->getView();
    
    $this->pluginBroker = new plugins\PluginBroker();
    
    $this->name = $name;
    
    $fullName = 'controllers\\' . $name . 'Controller';
    $this->namespace = substr(get_called_class(), 0, -strlen($fullName)-1);
    
    parent::__construct($module);
  }
  
  /**
   * Returns the view the controller must use to render.
   * @return View The view the controller must use to render.
   */
  public function getView() {
    return $this->view;
  }
  
  /**
   * Calls the action with the specfied name. This runs the plugin broker and
   * before- and afterAction functions.
   * @param strinig $actionName The name of the action to call, without the
   * 'action' prefix. Null to call the index action.
   * @return Controller This instance for fluid design.
   */
  public function call($actionName) {
    if ($actionName === null) {
      $actionName = $this->indexActionName;
    }
    
    $this->request = $this->application->getRequest();
    $this->response = $this->application->getResponse();
    
    // call 'onActionCall' on all controller plugins
    $this->pluginBroker->invoke('onActionCall', array($this, &$actionName));
    
    // if the request was marked for redispatch, exit this function
    if ($this->request->getRedispatch()) {
      return $this;
    }
    
    // remember the called action name
    $this->calledActionName = $actionName;
    
    // call the before action
    $this->beforeAction($actionName);

    // if the request was marked for redispatch, exit this function
    if ($this->request->getRedispatch()) {
      $this->calledActionName = null;
      return $this;
    }
    
    // componse the action method name and call it.
    $action = 'action' . $actionName;
    $this->$action();
    
    // call the acter action, regardless of the request was marked for
    // redispatch, because the action is exectued
    $this->afterAction($actionName);
      
    // call the 'onActionCalled' on all controller plugins
    $this->pluginBroker->invoke('onActionCalled', array($this, $actionName));
    
    // clean up
    $this->calledActionName = null;
    return $this;
  }
  
  /**
   * Returns whether a action exists.
   * @param unknown_type $actionName The name of the action to check the
   * existence of.
   * @return boolean Whether the action exists.
   */
  public function isAction($actionName) {
    if ($actionName === null) {
      $actionName = $this->indexActionName;
    }
    return is_callable(array($this, 'action' . $actionName));
  }
  
  /**
   * Executed before an action is calleed.
   * @param string $actionName The name of the action called.
   */
  protected function beforeAction($actionName) { }
  
  /**
   * Executed after an action is called.
   * @param string $actionName The name of the action that was called.
   */
  protected function afterAction($actionName) { }
  
  /**
   * Returns the namespace of this controller.
   * @return string The namespace of this controller.
   */
  public function getNamespace() {
    return $this->namespace;
  }
  
  /**
   * Returns the name of this controller, without the 'Controller' postfix.
   * @return string The name of this controller, without the 'Controller'
   * postfix.
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Adds an controller plugin, optionally with a name.
   * @param plugins\ControllerPlugin $plugin The controller plugin to add.
   * @param string $name The name with which the plugin can retrieved.
   * @return Controller This instance for fluid design.
   */
  public function addPlugin(plugins\ControllerPlugin $plugin, $name=null) {
    $this->pluginBroker->addPlugin($plugin, $name);
    return $this;
  }
  
  /**
   * Returns the plugin with the specified name.
   * @param string $name The name of the plugin to get.
   * @return plugins\ControllerPlugin The plugin with the specified name, or
   * null if no plugin with that name exists.
   */
  public function getPlugin($name) {
    return $this->pluginBroker->getPlugin($name);
  }
  
}