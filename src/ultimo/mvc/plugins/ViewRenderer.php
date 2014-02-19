<?php

namespace ultimo\mvc\plugins;

class ViewRenderer implements ApplicationPlugin, ModulePlugin, ControllerPlugin {
  
  /**
   * Whether not to render.
   * @var boolean Whether not to render.
   */
  protected $disabled = false;
  
  /**
   * The name of the master template.
   * @var string
   */
  protected $master = 'master';
  
  /**
   * The theme, null if there is no theme.
   * @var string
   */
  protected $theme = null;
  
  /**
   * Actions are pushed onto the stack in onActionCall(), and popped in 
   * onActionCalled().This way actions calling other actions result in correct
   * rendering.
   * @var array.
   */
  protected $actionStack = array();
  
  /**
   * Sets the theme.
   * @param string $theme The theme, or null if no theme should be used.
   */
  public function setTheme($theme) {
    $this->theme = $theme;
  }
  
  /**
   * Returns the theme.
   * @return string The theme, or null if no theme should be used.
   */
  public function getTheme() {
    return $this->theme;
  }
  
  public function onPluginAdded(\ultimo\mvc\Application $application) { }
  
  /**
   * Attach itself to the created module, and created a view.
   */
  public function onModuleCreated(\ultimo\mvc\Module $module) {
    $module->addPlugin($this, 'viewRenderer');
    $module->setView(new \ultimo\phptpl\mvc\View($module, $this->theme));
  }
  
  public function onRoute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) { }
  
  public function onRouted(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request=null) { }
  
  public function onDispatch(\ultimo\mvc\Application $application) { }
  
  public function onDispatched(\ultimo\mvc\Application $application) { }
  
  /**
   * Attach itselfs to the created controller.
   */
  public function onControllerCreated(\ultimo\mvc\Controller $controller) {
    $controller->addPlugin($this, 'viewRenderer');
  }
  
  /**
   * Sets the controller name to render for.
   * @param string $controller The controller name to render for.
   */
  public function setController($controller) {
    end($this->actionStack);
    $key = key($this->actionStack);
    $this->actionStack[$key]['controller'] = strtolower($controller);
  }
  
  /**
   * Sets the action name to render for.
   * @param strnig $action The action name to render for.
   */
  public function setAction($action) {
    end($this->actionStack);
    $key = key($this->actionStack);
    $this->actionStack[$key]['action'] = strtolower($action);
  }
  
  /**
   * Pushes the controller action onto the stack.
   */
  public function onActionCall(\ultimo\mvc\Controller $controller, &$actionName) {
    $action = array('controller' => strtolower($controller->getName()), 'action' => strtolower($actionName));
    array_push($this->actionStack, $action);
  }
  
  /**
   * Pops the controller action. If not disabled, it renders the template for
   * the controller action, and then the master template. The results are put
   * into the response.
   */
  public function onActionCalled(\ultimo\mvc\Controller $controller, $actionName) {
    $action = array_pop($this->actionStack);
    if ($controller->getApplication()->getRequest()->getRedispatch()) {
      return;
    }
    
    if ($this->isDisabled()) {
      return;
    }
    
    $response = $controller->getApplication()->getResponse();

    $view = $controller->getView();
    
    $content = $view->render($action['controller'] . '/' . $action['action']);
    
    if ($this->master !== null) {
      $view->content = $content;
      $content = $view->render($this->master);
    }
    
    $response->appendBody($content);
  }
  
  /**
   * Sets whether not to render.
   * @param boolean $disabled Whether not to render.
   */
  public function setDisabled($disabled) {
    $this->disabled = $disabled;
  }
  
  /**
   * Returns whether not to render.
   * @return boolean Whether not to render.
   */
  public function isDisabled() {
    return $this->disabled;
  }
  
  /**
   * Sets the name of the master template.
   * @param string $master The name of the master template.
   */
  public function setMaster($master) {
    $this->master = $master;
  }
  
  /**
   * Returns the name of the master template..
   * @return string The name of the master template.
   */
  public function getMaster() {
    return $this->master;
  }
  
}